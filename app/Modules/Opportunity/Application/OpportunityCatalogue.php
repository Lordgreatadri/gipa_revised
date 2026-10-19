<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Staff and investor reads. List queries select the columns the table needs
 * and paginate, so a large catalogue is not loaded into memory.
 */
final class OpportunityCatalogue
{
    public function __construct(private readonly DistrictScope $scope) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Opportunity>
     */
    public function forStaff(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Opportunity::query()
            ->select([
                'opportunities.id',
                'opportunities.uuid',
                'opportunities.reference',
                'opportunities.title',
                'opportunities.sector_id',
                'opportunities.workflow_state',
                'opportunities.project_status',
                'opportunities.district_id',
                'opportunities.investment_amount',
                'opportunities.currency',
                'opportunities.updated_at',
            ])
            ->with([
                'district:id,uuid,name,region_id',
                'district.region:id,name',
                'sector:id,name',
            ]);

        $this->scope->constrain($query, $actor);
        $this->applyFilters($query, $filters);
        $this->applyTab($query, (string) ($filters['tab'] ?? ''));

        [$column, $direction] = $this->sort($filters);

        return $query->orderBy($column, $direction)->orderByDesc('opportunities.id')->paginate(20)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Opportunity>
     */
    public function forInvestor(array $filters): LengthAwarePaginator
    {
        $query = Opportunity::query()
            ->investorVisible()
            ->select([
                'opportunities.id',
                'opportunities.uuid',
                'opportunities.reference',
                'opportunities.title',
                'opportunities.sector_id',
                'opportunities.sub_sector_id',
                'opportunities.enterprise_type_id',
                'opportunities.description',
                'opportunities.project_status',
                'opportunities.district_id',
                'opportunities.investment_amount',
                'opportunities.currency',
                'opportunities.enterprise_type_id',
            ])
            ->with([
                'district:id,name,region_id',
                'district.region:id,name',
                'sector:id,name',
                'subSector:id,name',
                'enterpriseType:id,name',
            ]);

        $this->applyFilters($query, $filters, investor: true);

        $direction = match ((string) ($filters['sort'] ?? '')) {
            'amount_asc' => 'asc',
            'amount_desc' => 'desc',
            default => null,
        };

        if ($direction === null) {
            return $query->orderByDesc('opportunities.id')->paginate(12)->withQueryString();
        }

        return $query->orderBy('opportunities.investment_amount', $direction)->orderByDesc('opportunities.id')->paginate(12)->withQueryString();
    }

    /**
     * @return array{
     *     counts: array<string, int>,
     *     total: int,
     *     currencies: array<string, float>,
     *     draftCount: int,
     *     recentDrafts: int,
     *     charts: array<string, mixed>
     * }
     */
    public function overview(User $actor): array
    {
        $counts = $this->stateCounts($actor);

        return [
            'counts' => $counts,
            'total' => array_sum($counts),
            'currencies' => $this->currencyTotals($actor),
            'draftCount' => $counts[WorkflowState::Draft->value] ?? 0,
            'recentDrafts' => $this->recentDrafts($actor),
            'charts' => [
                'sectors' => $this->countsBySector($actor),
                'regions' => $this->countsByRegion($actor),
                'states' => [
                    'labels' => array_map(fn (WorkflowState $state): string => $state->label(), WorkflowState::cases()),
                    'values' => array_map(fn (WorkflowState $state): int => $counts[$state->value] ?? 0, WorkflowState::cases()),
                ],
                'trend' => $this->dailyCounts($actor),
                'regional' => $this->regionalByQuarter($actor),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    public function stateCounts(User $actor): array
    {
        $query = Opportunity::query()->select('workflow_state')->selectRaw('count(*) as aggregate');
        $this->scope->constrain($query, $actor);

        $counts = [];

        foreach (WorkflowState::cases() as $state) {
            $counts[$state->value] = 0;
        }

        foreach ($query->groupBy('workflow_state')->get() as $row) {
            $counts[$row->workflow_state->value] = (int) $row->aggregate;
        }

        return $counts;
    }

    public function recentDrafts(User $actor): int
    {
        $query = Opportunity::query()
            ->where('workflow_state', WorkflowState::Draft->value)
            ->where('created_at', '>=', now()->subDays(2));
        $this->scope->constrain($query, $actor);

        return $query->count();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Opportunity>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters($query, array $filters, bool $investor = false): void
    {
        $table = $query->getModel()->getTable();
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $like = '%'.addcslashes(mb_substr($search, 0, 80), '%_\\').'%';
            $query->where(function ($inner) use ($table, $like): void {
                $inner->where($table.'.title', 'ilike', $like)
                    ->orWhereIn($table.'.district_id', District::query()->where('name', 'ilike', $like)->select('id'))
                    ->orWhereIn($table.'.sector_id', Sector::query()->where('name', 'ilike', $like)->select('id'));
            });
        }

        $sector = trim((string) ($filters['sector'] ?? ''));

        if ($sector !== '') {
            $query->whereIn($table.'.sector_id', Sector::query()->where('uuid', $sector)->select('id'));
        }

        $subSector = trim((string) ($filters['sub_sector'] ?? ''));

        if ($subSector !== '') {
            $query->whereIn($table.'.sub_sector_id', SubSector::query()->where('uuid', $subSector)->select('id'));
        }

        $type = trim((string) ($filters['type'] ?? ''));

        if ($type !== '') {
            $query->whereIn($table.'.enterprise_type_id', EnterpriseType::query()->where('uuid', $type)->select('id'));
        }

        $minimum = $filters['min_amount'] ?? null;

        if (is_numeric($minimum)) {
            $query->where($table.'.investment_amount', '>=', max(0, (float) $minimum));
        }

        $maximum = $filters['max_amount'] ?? null;

        if (is_numeric($maximum)) {
            $query->where($table.'.investment_amount', '<=', min(999999999999, (float) $maximum));
        }

        if (! $investor) {
            $state = WorkflowState::tryFrom((string) ($filters['state'] ?? ''));

            if ($state !== null && ! in_array((string) ($filters['tab'] ?? ''), ['drafts', 'cancelled'], true)) {
                $query->where($table.'.workflow_state', $state->value);
            }
        }

        $district = trim((string) ($filters['district'] ?? ''));

        if ($district !== '') {
            $query->whereIn($table.'.district_id', District::query()->where('uuid', $district)->select('id'));
        }

        $region = trim((string) ($filters['region'] ?? ''));

        if ($region !== '') {
            $query->whereIn(
                $table.'.district_id',
                District::query()->whereIn(
                    'region_id',
                    Region::query()->where('uuid', $region)->select('id'),
                )->select('id'),
            );
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Opportunity>  $query
     */
    private function applyTab($query, string $tab): void
    {
        $state = match ($tab) {
            'drafts' => WorkflowState::Draft,
            'cancelled' => WorkflowState::Cancelled,
            default => null,
        };

        if ($state !== null) {
            $query->where($query->getModel()->getTable().'.workflow_state', $state->value);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: string, 1: string}
     */
    private function sort(array $filters): array
    {
        $column = match ((string) ($filters['sort'] ?? '')) {
            'title' => 'opportunities.title',
            'amount' => 'opportunities.investment_amount',
            default => 'opportunities.updated_at',
        };

        $direction = (string) ($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return [$column, $direction];
    }

    /**
     * @return array<string, float>
     */
    private function currencyTotals(User $actor): array
    {
        $query = Opportunity::query()
            ->select('currency')
            ->selectRaw('coalesce(sum(investment_amount), 0) as total')
            ->groupBy('currency');
        $this->scope->constrain($query, $actor);

        $totals = ['GHS' => 0.0, 'USD' => 0.0];

        foreach ($query->get() as $row) {
            $totals[(string) $row->currency] = (float) $row->total;
        }

        return $totals;
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function countsBySector(User $actor): array
    {
        $query = Opportunity::query()
            ->join('sectors', 'sectors.id', '=', 'opportunities.sector_id')
            ->select('sectors.name')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('sectors.name')
            ->orderByDesc('aggregate');
        $this->scope->constrain($query, $actor);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->map(fn ($row): int => (int) $row->aggregate)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function countsByRegion(User $actor): array
    {
        $query = Opportunity::query()
            ->join('districts', 'districts.id', '=', 'opportunities.district_id')
            ->join('regions', 'regions.id', '=', 'districts.region_id')
            ->select('regions.name')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('regions.name')
            ->orderByDesc('aggregate')
            ->limit(8);
        $this->scope->constrain($query, $actor);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->map(fn ($row): int => (int) $row->aggregate)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function dailyCounts(User $actor): array
    {
        $start = now()->startOfDay()->subDays(13);
        $query = Opportunity::query()
            ->where('opportunities.created_at', '>=', $start)
            ->selectRaw('date(opportunities.created_at) as day')
            ->selectRaw('count(*) as aggregate')
            ->groupByRaw('date(opportunities.created_at)');
        $this->scope->constrain($query, $actor);

        $indexed = [];

        foreach ($query->get() as $row) {
            $indexed[(string) Carbon::parse($row->day)->toDateString()] = (int) $row->aggregate;
        }

        $labels = [];
        $values = [];

        for ($offset = 0; $offset < 14; $offset++) {
            $day = $start->copy()->addDays($offset);
            $labels[] = $day->format('d M');
            $values[] = $indexed[$day->toDateString()] ?? 0;
        }

        return compact('labels', 'values');
    }

    /**
     * Opportunities entered this year, split by the quarter they were created
     * and by their current workflow state, for every region.
     *
     * @return array{
     *     year: int,
     *     current: int,
     *     regions: list<string>,
     *     quarters: array<string, array<string, list<int>>>
     * }
     */
    private function regionalByQuarter(User $actor): array
    {
        $year = (int) now()->year;
        $states = [WorkflowState::Draft, WorkflowState::Approved, WorkflowState::Active, WorkflowState::Cancelled];
        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $query = Opportunity::query()
            ->join('districts', 'districts.id', '=', 'opportunities.district_id')
            ->join('regions', 'regions.id', '=', 'districts.region_id')
            ->whereYear('opportunities.created_at', $year)
            ->whereIn('opportunities.workflow_state', array_map(fn (WorkflowState $state): string => $state->value, $states))
            ->select('regions.id')
            ->selectRaw('extract(quarter from opportunities.created_at)::int as quarter')
            ->selectRaw('opportunities.workflow_state')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('regions.id')
            ->groupByRaw('extract(quarter from opportunities.created_at)::int')
            ->groupBy('opportunities.workflow_state');
        $this->scope->constrain($query, $actor);

        $matrix = [];

        foreach ($query->get() as $row) {
            $state = $row->workflow_state instanceof WorkflowState ? $row->workflow_state->value : (string) $row->workflow_state;
            $matrix[(int) $row->quarter][$state][(int) $row->id] = (int) $row->aggregate;
        }

        $quarters = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $series = [];

            foreach ($states as $state) {
                $series[$state->value] = $regions->map(
                    fn (Region $region): int => $matrix[$quarter][$state->value][$region->id] ?? 0,
                )->all();
            }

            $quarters[(string) $quarter] = $series;
        }

        return [
            'year' => $year,
            'current' => (int) ceil(now()->month / 3),
            'regions' => $regions->pluck('name')->all(),
            'quarters' => $quarters,
        ];
    }
}
