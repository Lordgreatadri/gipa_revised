<?php

namespace App\Modules\Shared\Presentation\Http\Controllers\Api\V1;

use App\Modules\InvestorEngagement\Application\SubmitEngagement;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementKind;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\Opportunity\Application\OpportunityCatalogue;
use App\Modules\Opportunity\Application\OpportunityMap;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Shared\Presentation\Http\Api\ApiEnvelope;
use App\Modules\Shared\Presentation\Http\Api\OpportunityPresenter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityApiController
{
    public function index(Request $request, OpportunityCatalogue $catalogue): JsonResponse
    {
        $filters = $request->only([
            'q', 'sector', 'sub_sector', 'type', 'region', 'district', 'min_amount', 'max_amount', 'sort', 'page',
        ]);

        if (isset($filters['sort']) && ! in_array($filters['sort'], ['amount_asc', 'amount_desc'], true)) {
            unset($filters['sort']);
        }

        $page = $catalogue->forInvestor($filters);

        return ApiEnvelope::page($page, fn (Opportunity $opportunity): array => OpportunityPresenter::summary($opportunity));
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        abort_unless($opportunity->workflow_state->isInvestorVisible(), 404);

        $opportunity->load([
            'district:id,name,region_id',
            'district.region:id,name',
            'sector:id,name',
            'subSector:id,name',
            'enterpriseType:id,name',
        ]);

        return ApiEnvelope::data(OpportunityPresenter::detail($opportunity));
    }

    public function inquire(Request $request, Opportunity $opportunity, SubmitEngagement $engagement): JsonResponse
    {
        return $this->storeEngagement($request, $opportunity, $engagement, EngagementKind::Inquiry, required: true);
    }

    public function interest(Request $request, Opportunity $opportunity, SubmitEngagement $engagement): JsonResponse
    {
        return $this->storeEngagement($request, $opportunity, $engagement, EngagementKind::Interest, required: false);
    }

    public function viewport(Request $request, OpportunityMap $map): JsonResponse
    {
        return ApiEnvelope::data($this->withoutWebUrl($map->viewport($request->only([
            'minLng', 'minLat', 'maxLng', 'maxLat', 'sector', 'sub_sector', 'type', 'region', 'district',
        ]))));
    }

    public function nearby(Request $request, OpportunityMap $map): JsonResponse
    {
        return ApiEnvelope::data($this->withoutWebUrl($map->nearby($request->only([
            'lat', 'lng', 'metres', 'sector', 'sub_sector', 'type', 'region', 'district',
        ]))));
    }

    public function engagements(Request $request): JsonResponse
    {
        $page = InvestorEngagement::query()
            ->where('investor_id', $request->user()->id)
            ->with('opportunity:id,uuid,title,reference')
            ->latest('id')
            ->paginate(12);

        return ApiEnvelope::page($page, fn (InvestorEngagement $engagement): array => $this->engagement($engagement));
    }

    private function storeEngagement(Request $request, Opportunity $opportunity, SubmitEngagement $engagement, EngagementKind $kind, bool $required): JsonResponse
    {
        $data = $request->validate([
            'message' => $required
                ? ['required', 'string', 'min:20', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        try {
            $record = $engagement->submit($request->user(), $opportunity, $kind, $data['message'] ?? null);
        } catch (AuthorizationException) {
            abort(403, 'This action is not allowed.');
        }

        $record->load('opportunity:id,uuid,title,reference');

        return ApiEnvelope::data($this->engagement($record), status: 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function engagement(InvestorEngagement $engagement): array
    {
        return [
            'id' => $engagement->uuid,
            'reference' => $engagement->reference,
            'kind' => $engagement->kind->value,
            'state' => $engagement->state->value,
            'message' => $engagement->message,
            'submitted_at' => $engagement->submitted_at?->toIso8601String(),
            'opportunity' => [
                'id' => $engagement->opportunity?->uuid,
                'title' => $engagement->opportunity?->title,
                'reference' => $engagement->opportunity?->reference,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $collection
     * @return array<string, mixed>
     */
    private function withoutWebUrl(array $collection): array
    {
        $collection['features'] = array_map(function (array $feature): array {
            unset($feature['properties']['url']);

            return $feature;
        }, $collection['features'] ?? []);

        return $collection;
    }
}
