<?php

namespace Tests;

use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeCountry(array $overrides = []): Country
    {
        return Country::query()->create(array_merge([
            'name' => 'Ghana',
            'code' => '+233',
            'currency' => 'Cedi',
            'currency_symbol' => 'GHS',
            'country_currency' => 'GH_GHS',
            'abbreviation' => 'GH',
            'icon_url' => '/media/icons/flags/ghana_flag.png',
        ], $overrides));
    }

    protected function giveBusinessProfile(User $investor): InvestorProfile
    {
        $profile = new InvestorProfile;
        $profile->forceFill([
            'organization_name' => $investor->name,
            'business_email' => $investor->email,
            'contact_phone' => $investor->phone,
            'profile_type' => ProfileType::Individual,
            'nationality' => 'Ghana',
            'country_code' => 'GH',
            'currency' => 'GHS',
            'preferred_language' => 'en',
            'onboarding_state' => ProfileOnboardingState::PendingApproval,
            'status' => ProfileStatus::Active,
        ]);
        $profile->user()->associate($investor);
        $profile->save();

        return $profile;
    }

    protected function assertAuditRecorded(string $action, ?int $subjectId = null): AuditRecord
    {
        $query = AuditRecord::query()->where('event', $action);

        if ($subjectId !== null) {
            $query->where('subject_id', $subjectId);
        }

        $record = $query->latest('id')->first();

        $this->assertNotNull($record, "Expected an audit record for [{$action}].");

        return $record;
    }

    protected function assertAuditNotRecorded(string $action): void
    {
        $this->assertSame(0, AuditRecord::query()->where('event', $action)->count(), "Did not expect an audit record for [{$action}].");
    }
}
