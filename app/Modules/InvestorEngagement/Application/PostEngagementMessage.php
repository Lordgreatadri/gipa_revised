<?php

namespace App\Modules\InvestorEngagement\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Models\EngagementMessage;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\Opportunity\Application\DistrictScope;
use App\Modules\Shared\Application\NotifyUser;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * A message on an inquiry or interest. It does not change the engagement state.
 */
final class PostEngagementMessage
{
    public const POSTED = 'engagement.message_posted';

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly NotifyUser $notify,
        private readonly DistrictScope $scope,
    ) {}

    public function post(User $author, InvestorEngagement $engagement, string $body): EngagementMessage
    {
        $this->authorize($author, $engagement);

        $message = new EngagementMessage;
        $message->forceFill(['body' => $body]);
        $message->engagement()->associate($engagement);
        $message->author()->associate($author);
        $message->save();

        $this->audit->record(
            AuditEntry::for(self::POSTED, $engagement)
                ->causedBy($author)
                ->withMetadata(['reference' => $engagement->reference])
        );

        if ($author->isInvestor()) {
            $this->notify->sendToPermission(
                AccessPermissions::ENGAGEMENT_REVIEW,
                'Investor message on '.$engagement->reference,
                $author->name.' added a message.',
                route('admin.engagement.show', $engagement),
            );
        } elseif ($engagement->investor !== null) {
            $this->notify->send(
                $engagement->investor,
                'Message on '.$engagement->reference,
                'GIPA added a message to your '.$engagement->kind->label().'.',
                route('investor.engagement.show', $engagement),
            );
        }

        return $message;
    }

    public function authorize(User $author, InvestorEngagement $engagement): void
    {
        if ($author->isInvestor() && $engagement->investor_id === $author->id) {
            return;
        }

        if ($author->isStaff() && $author->can(AccessPermissions::ENGAGEMENT_REVIEW)) {
            $opportunity = $engagement->opportunity;

            if ($opportunity === null || ! $this->scope->canSee($author, $opportunity)) {
                throw new AuthorizationException;
            }

            return;
        }

        throw new AuthorizationException;
    }
}
