<?php

namespace App\Modules\Opportunity\Presentation\Http\Controllers;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\InvestorEngagement\Application\SubmitEngagement;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementKind;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Application\OpportunityCatalogue;
use App\Modules\Opportunity\Application\OpportunityMap;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestorOpportunityController
{
    public function index(Request $request, OpportunityCatalogue $catalogue): View
    {
        $region = (string) $request->query('region', '');

        return view('investor.opportunities.index', [
            'opportunities' => $catalogue->forInvestor($request->query()),
            'regions' => Region::query()->orderBy('name')->get(['id', 'uuid', 'name']),
            'districts' => District::query()->with('region:id,uuid')->orderBy('name')->get(['id', 'uuid', 'name', 'region_id']),
            'sectors' => Sector::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'uuid', 'name']),
            'subSectors' => SubSector::query()->where('is_active', true)->orderBy('sort_order')->get(['uuid', 'name', 'sector_id']),
            'types' => EnterpriseType::query()->where('is_active', true)->orderBy('name')->get(['uuid', 'name']),
            'selectedRegion' => $region,
        ]);
    }

    public function show(Opportunity $opportunity): View
    {
        abort_unless($opportunity->workflow_state->isInvestorVisible(), 404);

        $opportunity->load([
            'district:id,name,region_id',
            'district.region:id,name',
            'sector:id,name',
            'subSector:id,name',
            'enterpriseType:id,name',
        ]);

        return view('investor.opportunities.show', [
            'opportunity' => $opportunity,
        ]);
    }

    public function map(): View
    {
        return view('investor.map', [
            'regions' => Region::query()->orderBy('name')->get(['uuid', 'name']),
            'sectors' => Sector::query()->where('is_active', true)->orderBy('sort_order')->get(['uuid', 'name']),
        ]);
    }

    public function geojson(Request $request, OpportunityMap $map): JsonResponse
    {
        return response()->json($map->viewport($request->query()));
    }

    public function nearby(Request $request, OpportunityMap $map): JsonResponse
    {
        return response()->json($map->nearby($request->query()));
    }

    public function inquire(Request $request, Opportunity $opportunity, SubmitEngagement $engagement): RedirectResponse
    {
        return $this->storeEngagement($request, $opportunity, $engagement, EngagementKind::Inquiry, required: true);
    }

    public function interest(Request $request, Opportunity $opportunity, SubmitEngagement $engagement): RedirectResponse
    {
        return $this->storeEngagement($request, $opportunity, $engagement, EngagementKind::Interest, required: false);
    }

    private function storeEngagement(Request $request, Opportunity $opportunity, SubmitEngagement $engagement, EngagementKind $kind, bool $required): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('status', 'Your '.$kind->label().' was recorded.');
        }

        $rules = [
            'message' => $required
                ? ['required', 'string', 'min:20', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ];

        $data = $request->validate($rules);

        /** @var User $investor */
        $investor = $request->user();
        $record = $engagement->submit($investor, $opportunity, $kind, $data['message'] ?? null);

        return redirect()
            ->route('investor.opportunities.show', $opportunity)
            ->with('status', $kind->label().' received. Reference '.$record->reference.'.');
    }
}
