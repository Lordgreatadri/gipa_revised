<?php

namespace App\Modules\InvestorOnboarding\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Shared\Application\NotifyUser;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageInvestorProfile
{
    public const SAVED = 'investor.profile_saved';

    public const APPROVED = 'investor.profile_approved';

    public const DELETED = 'investor.profile_deleted';

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly NotifyUser $notify,
    ) {}

    /**
     * @param  array{organization_name: string, business_email: string, contact_phone: string, profile_type: string, country: string, currency: string, preferred_language: string}  $data
     */
    public function save(User $investor, array $data): InvestorProfile
    {
        $country = Country::query()->where('uuid', $data['country'])->firstOrFail();
        $profile = $investor->investorProfile ?: new InvestorProfile;
        $wasApproved = $profile->exists && $profile->canEngage();
        $materialChange = $profile->exists && (
            $profile->organization_name !== $data['organization_name']
            || strcasecmp((string) $profile->business_email, $data['business_email']) !== 0
        );

        $profile->forceFill([
            'organization_name' => $data['organization_name'],
            'business_email' => mb_strtolower($data['business_email']),
            'contact_phone' => $data['contact_phone'],
            'profile_type' => ProfileType::from($data['profile_type']),
            'nationality' => $country->name,
            'country_code' => $country->abbreviation,
            'currency' => $data['currency'],
            'preferred_language' => $data['preferred_language'],
            'status' => $profile->status ?? ProfileStatus::Active,
            'onboarding_state' => ($wasApproved && ! $materialChange)
                ? ProfileOnboardingState::Approved
                : ProfileOnboardingState::PendingApproval,
        ]);
        $profile->user()->associate($investor);
        $profile->save();

        $this->audit->record(AuditEntry::for(self::SAVED, $profile)->causedBy($investor));

        if ($profile->onboarding_state === ProfileOnboardingState::PendingApproval) {
            $this->notify->sendToPermission(
                AccessPermissions::PROFILES_REVIEW,
                'Investor profile awaiting approval',
                $investor->name.' submitted a business profile.',
                route('admin.profiles.index'),
            );
        }

        return $profile;
    }

    public function approve(User $actor, InvestorProfile $profile): void
    {
        if (! $actor->can(AccessPermissions::PROFILES_REVIEW)) {
            throw new AuthorizationException;
        }

        $profile->forceFill([
            'onboarding_state' => ProfileOnboardingState::Approved,
            'status' => ProfileStatus::Active,
        ])->save();

        $this->audit->record(AuditEntry::for(self::APPROVED, $profile)->causedBy($actor));
        $this->notify->send(
            $profile->user,
            'Business profile approved',
            'You can now send inquiries and express interest in active opportunities.',
            route('investor.opportunities'),
        );
    }

    public function delete(User $investor, bool $consent): void
    {
        $profile = $investor->investorProfile;

        if ($profile === null) {
            throw ValidationException::withMessages(['consent' => 'There is no business profile to remove.']);
        }

        if (! $consent) {
            throw ValidationException::withMessages(['consent' => 'Confirm that you want to remove this profile.']);
        }

        $open = InvestorEngagement::query()->where('investor_id', $investor->id)->exists();

        if ($open) {
            throw ValidationException::withMessages([
                'consent' => 'This profile still has an inquiry or interest with GIPA, so it cannot be removed yet.',
            ]);
        }

        DB::transaction(function () use ($investor, $profile): void {
            $profile->forceFill(['consent_to_delete' => true])->save();
            $profile->delete();
            $this->audit->record(AuditEntry::for(self::DELETED, $profile)->causedBy($investor));
        });
    }

    public function touchEngagement(User $investor): void
    {
        $investor->investorProfile?->forceFill(['last_engaged_at' => now()])->save();
    }
}
