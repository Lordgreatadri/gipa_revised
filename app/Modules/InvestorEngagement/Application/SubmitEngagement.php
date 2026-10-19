<?php

namespace App\Modules\InvestorEngagement\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\EngagementAuditActions;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementKind;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementState;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\InvestorOnboarding\Application\ManageInvestorProfile;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Shared\Application\NotifyUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SubmitEngagement
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly ManageInvestorProfile $profiles,
        private readonly NotifyUser $notify,
    ) {}

    public function submit(User $investor, Opportunity $opportunity, EngagementKind $kind, ?string $message): InvestorEngagement
    {
        if (! $investor->can(AccessPermissions::INVESTOR_ENGAGEMENT) || ! $opportunity->workflow_state->isInvestorVisible()) {
            throw new AuthorizationException;
        }

        $profile = $investor->investorProfile;

        if ($profile === null || ! $profile->canEngage()) {
            throw ValidationException::withMessages([
                'message' => 'Create your business profile and wait for GIPA approval before sending an inquiry or expressing interest.',
            ]);
        }

        $existing = InvestorEngagement::query()
            ->where('investor_id', $investor->id)
            ->where('opportunity_id', $opportunity->id)
            ->where('kind', $kind->value)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'message' => 'You already have a '.$kind->label().' on this opportunity. Its reference is '.$existing->reference.'.',
            ]);
        }

        return DB::transaction(function () use ($investor, $opportunity, $kind, $message): InvestorEngagement {
            $engagement = new InvestorEngagement;
            $engagement->forceFill([
                'kind' => $kind,
                'state' => EngagementState::Submitted,
                'reference' => $this->reference($kind),
                'message' => $message,
                'submitted_at' => now(),
            ]);
            $engagement->investor()->associate($investor);
            $engagement->opportunity()->associate($opportunity);
            $engagement->save();

            $this->audit->record(
                AuditEntry::for(EngagementAuditActions::SUBMITTED, $engagement)
                    ->causedBy($investor)
                    ->withMetadata([
                        'reference' => $engagement->reference,
                        'kind' => $kind->value,
                        'opportunity' => $opportunity->reference,
                    ])
            );

            $this->profiles->touchEngagement($investor);
            $this->notify->sendToPermission(
                AccessPermissions::ENGAGEMENT_REVIEW,
                $kind->label().' received',
                $investor->name.' submitted '.$engagement->reference.' on '.$opportunity->reference.'.',
                route('admin.engagement.show', $engagement),
            );

            return $engagement;
        });
    }

    private function reference(EngagementKind $kind): string
    {
        $prefix = ($kind === EngagementKind::Inquiry ? 'INQ' : 'INT').'-'.now()->year.'-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = $prefix.strtoupper(Str::random(6));

            if (! InvestorEngagement::query()->where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw ValidationException::withMessages([
            'message' => 'A reference could not be reserved. Submit again.',
        ]);
    }
}
