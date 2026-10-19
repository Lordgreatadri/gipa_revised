<?php

namespace App\Modules\InvestorEngagement\Application;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementKind;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Support\Collection;

/**
 * Figures for the investor overview. Counts stay as queries. Currencies
 * are kept apart because GHS and USD are not added together.
 */
final class InvestorOverview
{
    /**
     * @return array{
     *     active: int,
     *     inquiries: int,
     *     interests: int,
     *     pipeline: Collection<int, object>,
     *     recent: Collection<int, InvestorEngagement>
     * }
     */
    public function for(User $user): array
    {
        $engagements = InvestorEngagement::query()->where('investor_id', $user->id);

        return [
            'active' => Opportunity::query()->investorVisible()->count(),
            'inquiries' => (clone $engagements)->where('kind', EngagementKind::Inquiry)->count(),
            'interests' => (clone $engagements)->where('kind', EngagementKind::Interest)->count(),
            'pipeline' => Opportunity::query()
                ->investorVisible()
                ->selectRaw('currency, count(*) as total, coalesce(sum(investment_amount), 0) as amount')
                ->groupBy('currency')
                ->orderBy('currency')
                ->get(),
            'recent' => InvestorEngagement::query()
                ->where('investor_id', $user->id)
                ->with(['opportunity' => fn ($query) => $query->select('id', 'uuid', 'title')])
                ->latest('submitted_at')
                ->limit(4)
                ->get(),
        ];
    }
}
