<?php

namespace App\Modules\PublicPortal\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\PublicPortal\Domain\Models\ContactReply;
use App\Modules\PublicPortal\Domain\Models\ContactUs;
use App\Modules\PublicPortal\Infrastructure\Notifications\ContactEnquiryReplied;
use App\Modules\Shared\Application\NotifyUser;
use App\Modules\Shared\Infrastructure\Sms\SmsGateway;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ManageContactEnquiry
{
    public const READ = 'contact.read';

    public const REPLIED = 'contact.replied';

    public const CLOSED = 'contact.closed';

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly NotifyUser $notify,
        private readonly SmsGateway $sms,
    ) {}

    public function markRead(User $actor, ContactUs $enquiry): void
    {
        if ($enquiry->status !== ContactUs::STATUS_NEW) {
            return;
        }

        $enquiry->forceFill([
            'status' => ContactUs::STATUS_READ,
            'read_by' => $actor->uuid,
            'read_at' => now(),
        ])->save();

        $this->audit->record(AuditEntry::for(self::READ, $enquiry)->causedBy($actor)->withMetadata(['reference' => $enquiry->reference]));
    }

    public function reply(User $actor, ContactUs $enquiry, string $body): void
    {
        if (! $actor->can(AccessPermissions::CONTACT_MANAGE)) {
            throw new AuthorizationException;
        }

        DB::transaction(function () use ($actor, $enquiry, $body): void {
            $reply = new ContactReply;
            $reply->forceFill(['body' => $body]);
            $reply->enquiry()->associate($enquiry);
            $reply->author()->associate($actor);
            $reply->save();

            $enquiry->forceFill([
                'status' => ContactUs::STATUS_RESPONDED,
                'responded_at' => now(),
                'responded_by' => $actor->id,
            ])->save();

            $this->audit->record(AuditEntry::for(self::REPLIED, $enquiry)->causedBy($actor)->withMetadata(['reference' => $enquiry->reference]));
        });

        $enquiry->notify(new ContactEnquiryReplied($enquiry, $body));
        $this->sms->queue($enquiry->phone_number, 'GIPA replied to '.$enquiry->reference.'. Please check your e-mail.', 'contact_reply');
    }

    public function close(User $actor, ContactUs $enquiry): void
    {
        if (! $actor->can(AccessPermissions::CONTACT_MANAGE)) {
            throw new AuthorizationException;
        }

        $enquiry->forceFill(['status' => ContactUs::STATUS_CLOSED])->save();
        $this->audit->record(AuditEntry::for(self::CLOSED, $enquiry)->causedBy($actor)->withMetadata(['reference' => $enquiry->reference]));
    }

    public function notifyStaff(ContactUs $enquiry): void
    {
        $this->notify->sendToPermission(
            AccessPermissions::CONTACT_VIEW,
            'New contact enquiry',
            $enquiry->reference.': '.$enquiry->subject,
            route('admin.contact.show', $enquiry),
        );
        $this->sms->queue(
            $enquiry->phone_number,
            'GIPA has received your enquiry '.$enquiry->reference.'. A member of the team will be in touch.',
            'contact_acknowledgement',
        );
    }
}
