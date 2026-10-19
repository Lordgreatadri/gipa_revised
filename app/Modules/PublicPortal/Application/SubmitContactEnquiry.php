<?php

namespace App\Modules\PublicPortal\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\PublicPortal\Domain\ContactAuditActions;
use App\Modules\PublicPortal\Domain\Models\ContactUs;
use App\Modules\PublicPortal\Infrastructure\Notifications\ContactEnquiryAcknowledged;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Public Contact us journey: store the enquiry and e-mail an acknowledgement.
 * SMS and staff-inbox fan-out stay with the later management slice.
 */
final class SubmitContactEnquiry
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly ConnectionInterface $db,
        private readonly ManageContactEnquiry $inbox,
    ) {}

    /**
     * @param  array{name: string, email: string, phone_number: ?string, available_time_for_call: ?string, subject: string, message: string}  $data
     */
    public function handle(array $data, ?string $ipAddress): ContactUs
    {
        return $this->db->transaction(function () use ($data, $ipAddress): ContactUs {
            $enquiry = ContactUs::query()->create([
                'reference' => $this->reference(),
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'phone_number' => $data['phone_number'] ?: null,
                'available_time_for_call' => $data['available_time_for_call'] ?: null,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'ip_address' => $ipAddress,
                'status' => ContactUs::STATUS_NEW,
            ]);

            $enquiry->notify(new ContactEnquiryAcknowledged($enquiry));

            $enquiry->forceFill([
                'email_sent' => true,
                'email_sent_at' => now(),
                'sms_sent' => false,
                'sms_sent_at' => null,
            ])->save();

            $this->audit->record(
                AuditEntry::for(ContactAuditActions::ENQUIRY_SUBMITTED, $enquiry)
                    ->withMetadata([
                        'reference' => $enquiry->reference,
                        'subject' => $enquiry->subject,
                        'has_phone' => filled($enquiry->phone_number),
                    ])
            );

            DB::afterCommit(function () use ($enquiry): void {
                $this->inbox->notifyStaff($enquiry);
            });

            return $enquiry->refresh();
        });
    }

    private function reference(): string
    {
        return 'CON-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
