<?php

namespace App\Modules\Geography\Presentation\Http\Controllers;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Application\DistrictScope;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeographyController
{
    public function __construct(private readonly DistrictScope $scope) {}

    public function regions(Request $request): View
    {
        /** @var User $actor */
        $actor = $request->user();
        $term = trim((string) $request->query('q', ''));

        return view('admin.geography.regions', [
            'regions' => $this->regionQuery($actor, $term)->paginate(20)->withQueryString(),
            'summary' => [
                'regions' => $this->regionQuery($actor)->count(),
                'districts' => $this->districtQuery($actor)->count(),
                'opportunities' => $this->opportunityCount($actor),
            ],
        ]);
    }

    public function districts(Request $request): View
    {
        /** @var User $actor */
        $actor = $request->user();
        $byRegion = $this->districtQuery($actor)
            ->join('regions', 'regions.id', '=', 'districts.region_id')
            ->select('regions.name')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('regions.name')
            ->orderByDesc('aggregate')
            ->get();

        return view('admin.geography.districts', [
            'summary' => [
                'regions' => $this->regionQuery($actor)->count(),
                'districts' => $this->districtQuery($actor)->count(),
                'withOpportunities' => $this->districtQuery($actor)->whereHas('opportunities', function (Builder $query) use ($actor): void {
                    $this->scope->constrain($query, $actor);
                })->count(),
            ],
            'chart' => [
                'labels' => $byRegion->pluck('name')->all(),
                'values' => $byRegion->map(fn ($row): int => (int) $row->aggregate)->all(),
                'series' => 'Districts',
            ],
        ]);
    }

    public function districtRegister(Request $request): View
    {
        /** @var User $actor */
        $actor = $request->user();

        return view('admin.geography.district-register', [
            'districts' => $this->districtList($actor, $request),
            'regions' => $this->regionQuery($actor)->orderBy('name')->get(['id', 'uuid', 'name']),
        ]);
    }

    /**
     * @return Builder<Region>
     */
    private function regionQuery(User $actor, string $term = ''): Builder
    {
        $query = Region::query()
            ->withCount([
                'districts' => function (Builder $districts) use ($actor): void {
                    if ($this->scope->isRestricted($actor)) {
                        $districts->whereIn('districts.id', $actor->districts()->select('districts.id'));
                    }
                },
                'opportunities' => function (Builder $opportunities) use ($actor): void {
                    $this->scope->constrain($opportunities, $actor);
                },
            ]);

        if ($this->scope->isRestricted($actor)) {
            $query->whereIn('id', $actor->districts()->select('districts.region_id'));
        }

        if ($term !== '') {
            $like = '%'.addcslashes(mb_substr($term, 0, 80), '%_\\').'%';
            $query->where(function (Builder $inner) use ($like): void {
                $inner->where('name', 'ilike', $like)->orWhere('code', 'ilike', $like)->orWhere('capital', 'ilike', $like);
            });
        }

        return $query->orderBy('name');
    }

    /**
     * @return Builder<District>
     */
    private function districtQuery(User $actor): Builder
    {
        $query = District::query();

        if ($this->scope->isRestricted($actor)) {
            $query->whereIn('districts.id', $actor->districts()->select('districts.id'));
        }

        return $query;
    }

    /**
     * @return LengthAwarePaginator<int, District>
     */
    private function districtList(User $actor, Request $request): LengthAwarePaginator
    {
        $term = trim((string) $request->query('q', ''));
        $query = $this->districtQuery($actor)
            ->with('region:id,uuid,name')
            ->withCount(['opportunities' => function (Builder $opportunities) use ($actor): void {
                $this->scope->constrain($opportunities, $actor);
            }]);

        if ($term !== '') {
            $like = '%'.addcslashes(mb_substr($term, 0, 80), '%_\\').'%';
            $query->where(function (Builder $inner) use ($like): void {
                $inner->where('districts.name', 'ilike', $like)->orWhere('districts.code', 'ilike', $like);
            });
        }

        $region = trim((string) $request->query('region', ''));

        if ($region !== '') {
            $query->whereIn('region_id', Region::query()->where('uuid', $region)->select('id'));
        }

        $sort = match ((string) $request->query('sort')) {
            'name' => 'districts.name',
            'code' => 'districts.code',
            default => 'districts.name',
        };
        $direction = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction)->paginate(20)->withQueryString();
    }

    private function opportunityCount(User $actor): int
    {
        $query = Opportunity::query();
        $this->scope->constrain($query, $actor);

        return $query->count();
    }
}
