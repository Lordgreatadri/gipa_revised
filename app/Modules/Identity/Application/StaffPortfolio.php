<?php

namespace App\Modules\Identity\Application;

use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Opportunity\Application\DistrictScope;
use App\Modules\Opportunity\Application\OpportunityCatalogue;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\OpportunityTransition;
use App\Modules\PublicPortal\Domain\Models\ContactUs;

/**
 * Figures for the staff home. Opportunity numbers follow district scope.
 * Queues that belong to a permission are omitted when that permission is missing.
 * Readiness, jobs and infrastructure stay off this page until those fields exist.
 */
final class StaffPortfolio
{
    public function __construct(
        private readonly OpportunityCatalogue $catalogue,
        private readonly DistrictScope $scope,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $opportunities = $user->can(AccessPermissions::OPPORTUNITIES_VIEW)
            ? $this->catalogue->overview($user)
            : null;

        return [
            'restricted' => $this->scope->isRestricted($user),
            'accounts' => [
                'staff' => User::query()->where('user_type', UserType::Staff)->count(),
                'investors' => User::query()->where('user_type', UserType::Investor)->count(),
                'roles' => Role::query()->count(),
                'permissions' => Permission::query()->count(),
            ],
            'opportunities' => $opportunities,
            'profiles' => $user->can(AccessPermissions::PROFILES_REVIEW)
                ? InvestorProfile::query()->where('onboarding_state', ProfileOnboardingState::PendingApproval->value)->count()
                : null,
            'engagements' => $user->can(AccessPermissions::ENGAGEMENT_VIEW)
                ? $this->engagementCount($user)
                : null,
            'contacts' => $user->can(AccessPermissions::CONTACT_VIEW)
                ? ContactUs::query()->where('status', ContactUs::STATUS_NEW)->count()
                : null,
            'activity' => $opportunities === null ? collect() : $this->activity($user),
            'audit' => $user->can(AccessPermissions::AUDIT_VIEW)
                ? AuditRecord::query()->with('causer:id,name')->latest('id')->limit(6)->get()
                : null,
            'lifecycle' => $opportunities === null ? [] : collect(WorkflowState::cases())->map(fn (WorkflowState $state): array => [
                'label' => $state->label(),
                'value' => (int) ($opportunities['counts'][$state->value] ?? 0),
                'icon' => match ($state) {
                    WorkflowState::Draft => 'file-pen',
                    WorkflowState::PendingApproval => 'hourglass',
                    WorkflowState::Approved => 'badge-check',
                    WorkflowState::Active => 'circle-play',
                    WorkflowState::Completed => 'check-circle-2',
                    WorkflowState::Cancelled => 'ban',
                },
            ])->all(),
        ];
    }

    private function engagementCount(User $user): int
    {
        $query = InvestorEngagement::query();

        if ($this->scope->isRestricted($user)) {
            $visible = Opportunity::query()->select('opportunities.id');
            $this->scope->constrain($visible, $user);
            $query->whereIn('opportunity_id', $visible);
        }

        return $query->count();
    }

    /**
     * @return \Illuminate\Support\Collection<int, OpportunityTransition>
     */
    private function activity(User $user): \Illuminate\Support\Collection
    {
        $visible = Opportunity::query()->select('opportunities.id');
        $this->scope->constrain($visible, $user);

        return OpportunityTransition::query()
            ->with(['actor:id,name', 'opportunity:id,uuid,title,reference'])
            ->whereIn('opportunity_id', $visible)
            ->latest('id')
            ->limit(6)
            ->get();
    }
}
