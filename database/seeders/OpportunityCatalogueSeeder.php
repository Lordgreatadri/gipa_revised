<?php

namespace Database\Seeders;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\InvestmentStructure;
use App\Modules\Opportunity\Domain\Enums\ProjectStatus;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\OpportunityTransition;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Database\Seeder;
use App\Modules\Geography\Application\GhanaSamplePoint;

// use App\Modules\Geography\Application\GhanaSamplePoint;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;

/**
 * One hundred and sixty sample opportunities covering every workflow state
 * and the staff roles that move a record. Re-running does not add another
 * catalogue once any opportunity exists.
 */
class OpportunityCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('access.seed_demo_accounts') || Opportunity::query()->exists()) {
            return;
        }

        $districts = District::query()->with('region:id,code')->orderBy('id')->get(['id', 'region_id', 'code']);

        if ($districts->isEmpty()) {
            return;
        }

        $officer = User::role(AccessRoles::DISTRICT_OFFICER)->first();
        $agent = User::role(AccessRoles::FIELD_AGENT)->first();
        $manager = User::role(AccessRoles::CONTENT_MANAGER)->first();
        $reviewer = User::role(AccessRoles::REVIEWER)->first()
            ?? User::role(AccessRoles::SUPER_ADMINISTRATOR)->first();
        $creator = $manager ?? $officer ?? $agent ?? $reviewer;

        if ($creator === null || $reviewer === null) {
            return;
        }

        $officerDistrict = $officer?->districts()->value('districts.id');
        $agentDistrict = $agent?->districts()->value('districts.id');
        $states = array_merge(
            array_fill(0, 40, WorkflowState::Draft),
            array_fill(0, 30, WorkflowState::PendingApproval),
            array_fill(0, 20, WorkflowState::Approved),
            array_fill(0, 40, WorkflowState::Active),
            array_fill(0, 15, WorkflowState::Completed),
            array_fill(0, 15, WorkflowState::Cancelled),
        );
        $sectors = Sector::query()->where('is_active', true)->orderBy('sort_order')->get();
        $subSectors = SubSector::query()->where('is_active', true)->orderBy('sort_order')->get()->groupBy('sector_id');
        $enterprises = EnterpriseType::query()->where('is_active', true)->orderBy('id')->get();
        $structures = InvestmentStructure::cases();
        $now = now();
        $transitions = [];

        if ($sectors->isEmpty() || $enterprises->isEmpty()) {
            return;
        }

        foreach ($states as $index => $state) {
            $district = $this->districtFor($index, $state, $districts, $officerDistrict, $agentDistrict);
            [$latitude, $longitude] = GhanaSamplePoint::near((string) $district->region?->code, $index + 1);
            $owner = $this->owner($district->id, $officer, $officerDistrict, $agent, $agentDistrict, $creator);
            $amount = 250000 + (($index * 175000) % 20000000);
            $sector = $sectors[$index % $sectors->count()];
            $sectorSubs = $subSectors->get($sector->id, collect());
            $subSector = $sectorSubs[$index % max(1, $sectorSubs->count())] ?? null;

            if ($subSector === null) {
                continue;
            }

            $opportunity = new Opportunity;
            $opportunity->forceFill([
                'title' => 'Sample opportunity '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'project_status' => ProjectStatus::forWorkflow($state),
                'workflow_state' => $state,
                'description' => 'Sample record for workflow state '.$state->value.'. It is not a published investment commitment.',
                'objectives' => 'Demonstrate the staff journey from district entry to investor visibility.',
                'success_factors' => null,
                'competitive_advantages' => null,
                'location_description' => 'Sample site '.$district->code,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'investment_amount' => $amount,
                'currency' => $index % 2 === 0 ? 'GHS' : 'USD',
                'investment_structure' => $structures[$index % count($structures)],
                'roi' => 8 + ($index % 12),
                'irr' => null,
                'npv' => null,
                'payback_period_months' => 24 + ($index % 48),
                'projected_revenue' => $amount * 0.2,
                'contact_name' => 'Sample contact',
                'contact_organization' => 'Sample enterprise',
                'contact_phone' => '+23320000'.str_pad((string) ($index % 10000), 4, '0', STR_PAD_LEFT),
                'contact_email' => 'sample'.($index + 1).'@example.test',
                'reference' => 'IOMP-'.$now->year.'-'.$district->code.'-'.strtoupper(Str::random(6)),
                'created_by' => $owner->id,
                'assigned_to' => $owner->id,
                'submitted_at' => $state === WorkflowState::Draft ? null : $now,
                'approved_at' => in_array($state, [WorkflowState::Approved, WorkflowState::Active, WorkflowState::Completed], true) ? $now : null,
                'activated_at' => in_array($state, [WorkflowState::Active, WorkflowState::Completed], true) ? $now : null,
                'completed_at' => $state === WorkflowState::Completed ? $now : null,
                'cancelled_at' => $state === WorkflowState::Cancelled ? $now : null,
            ]);
            $opportunity->district()->associate($district);
            $opportunity->sector()->associate($sector);
            $opportunity->subSector()->associate($subSector);
            $opportunity->enterpriseType()->associate($enterprises[$index % $enterprises->count()]);
            $opportunity->save();

            foreach ($this->path($state) as [$action, $from, $to]) {
                $transitions[] = [
                    'opportunity_id' => $opportunity->id,
                    'from_state' => $from,
                    'to_state' => $to,
                    'action' => $action,
                    'actor_id' => in_array($action, ['approve', 'activate', 'complete'], true) ? $reviewer->id : $owner->id,
                    'assigned_to' => null,
                    'reason' => $action === 'cancel' ? 'Sample cancellation.' : null,
                    'created_at' => $now,
                ];
            }

            if (count($transitions) >= 80) {
                OpportunityTransition::query()->insert($transitions);
                $transitions = [];
            }
        }

        if ($transitions !== []) {
            OpportunityTransition::query()->insert($transitions);
        }


        $this->grantProfileReview();
        $this->placeSampleOpportunities();
        $this->backfillDemoProfiles();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, District>  $districts
     */
    private function districtFor(int $index, WorkflowState $state, $districts, ?int $officerDistrict, ?int $agentDistrict): District
    {
        if ($state === WorkflowState::Draft && $index < 20 && $officerDistrict !== null) {
            return $districts->firstWhere('id', $officerDistrict) ?? $districts[$index % $districts->count()];
        }

        if ($state === WorkflowState::Draft && $agentDistrict !== null && $index >= 20 && $index < 30) {
            return $districts->firstWhere('id', $agentDistrict) ?? $districts[$index % $districts->count()];
        }

        return $districts[$index % $districts->count()];
    }

    private function owner(int $districtId, ?User $officer, ?int $officerDistrict, ?User $agent, ?int $agentDistrict, User $fallback): User
    {
        if ($officer !== null && $districtId === $officerDistrict) {
            return $officer;
        }

        if ($agent !== null && $districtId === $agentDistrict) {
            return $agent;
        }

        return $fallback;
    }

    /**
     * @return list<array{0: string, 1: string|null, 2: string}>
     */
    private function path(WorkflowState $state): array
    {
        $steps = [
            ['record', null, WorkflowState::Draft->value],
        ];

        if ($state === WorkflowState::Draft) {
            return $steps;
        }

        $steps[] = ['submit', WorkflowState::Draft->value, WorkflowState::PendingApproval->value];

        if ($state === WorkflowState::PendingApproval) {
            return $steps;
        }

        if ($state === WorkflowState::Cancelled) {
            $steps[] = ['cancel', WorkflowState::PendingApproval->value, WorkflowState::Cancelled->value];

            return $steps;
        }

        $steps[] = ['approve', WorkflowState::PendingApproval->value, WorkflowState::Approved->value];

        if ($state === WorkflowState::Approved) {
            return $steps;
        }

        $steps[] = ['activate', WorkflowState::Approved->value, WorkflowState::Active->value];

        if ($state === WorkflowState::Completed) {
            $steps[] = ['complete', WorkflowState::Active->value, WorkflowState::Completed->value];
        }

        return $steps;
    }



    private function grantProfileReview(): void
    {
        $now = now();
        $permissionId = DB::table('permissions')->where('name', 'investor.profiles.review')->where('guard_name', 'web')->value('id');

        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => 'investor.profiles.review',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')->where('guard_name', 'web')->whereIn('name', ['Super Administrator', 'Reviewer / Approver'])->pluck('id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_has_permissions')->where('permission_id', $permissionId)->where('role_id', $roleId)->exists();

            if (! $exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    private function placeSampleOpportunities(): void
    {
        // if (! Schema::hasTable('opportunities') || ! Schema::hasColumn('opportunities', 'location')) {
        //     return;
        // }

        $rows = DB::table('opportunities')
            ->join('districts', 'districts.id', '=', 'opportunities.district_id')
            ->join('regions', 'regions.id', '=', 'districts.region_id')
            ->get(['opportunities.id', 'regions.code']);

        foreach ($rows as $row) {
            [$latitude, $longitude] = GhanaSamplePoint::near((string) $row->code, (int) $row->id);
            DB::update(
                'update opportunities set latitude = ?, longitude = ?, location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography where id = ?',
                [$latitude, $longitude, $longitude, $latitude, $row->id],
            );
        }
    }

    private function backfillDemoProfiles(): void
    {
        $investors = DB::table('users')
            ->where('user_type', UserType::Investor->value)
            ->where('email', 'like', '%@example.test')
            ->get(['id', 'name', 'email', 'phone']);

        foreach ($investors as $investor) {
            $exists = DB::table('investor_profiles')->where('user_id', $investor->id)->whereNull('deleted_at')->exists();

            if ($exists) {
                continue;
            }

            $now = now();
            DB::table('investor_profiles')->insert([
                'uuid' => (string) Str::uuid(),
                'user_id' => $investor->id,
                'organization_name' => $investor->name,
                'business_email' => $investor->email,
                'contact_phone' => $investor->phone,
                'profile_type' => ProfileType::Individual->value,
                'nationality' => 'Ghana',
                'country_code' => 'GH',
                'currency' => 'GHS',
                'preferred_language' => 'en',
                'onboarding_state' => ProfileOnboardingState::Approved->value,
                'status' => ProfileStatus::Active->value,
                'consent_to_delete' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
