<?php

namespace App\Modules\Opportunity\Presentation\Http\Controllers;

use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Application\DistrictScope;
use App\Modules\Opportunity\Application\OpportunityCatalogue;
use App\Modules\Opportunity\Application\RecordOpportunity;
use App\Modules\Opportunity\Application\StoreOpportunityDocument;
use App\Modules\Opportunity\Application\TransitionOpportunity;
use App\Modules\Opportunity\Domain\Enums\DocumentCategory;
use App\Modules\Opportunity\Domain\Enums\InvestmentStructure;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\OpportunityDocument;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use App\Modules\Opportunity\Domain\Workflow\OpportunityWorkflow;
use App\Modules\Opportunity\Presentation\Http\Requests\SaveOpportunityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OpportunityController
{
    public function index(Request $request, OpportunityCatalogue $catalogue): View
    {
        /** @var User $actor */
        $actor = $request->user();

        return view('admin.opportunities.index', [
            'overview' => $catalogue->overview($actor),
            'states' => WorkflowState::cases(),
        ]);
    }

    public function register(Request $request, OpportunityCatalogue $catalogue): View
    {
        /** @var User $actor */
        $actor = $request->user();
        $tab = in_array($request->query('tab'), ['drafts', 'cancelled'], true) ? (string) $request->query('tab') : 'all';

        return view('admin.opportunities.register', [
            'opportunities' => $catalogue->forStaff($actor, $request->query()),
            'counts' => $catalogue->stateCounts($actor),
            'recentDrafts' => $catalogue->recentDrafts($actor),
            'regions' => Region::query()->orderBy('name')->get(['id', 'uuid', 'name']),
            'sectors' => Sector::query()->where('is_active', true)->orderBy('sort_order')->get(['uuid', 'name']),
            'states' => WorkflowState::cases(),
            'tab' => $tab,
        ]);
    }

    public function create(Request $request, DistrictScope $scope): View
    {
        /** @var User $actor */
        $actor = $request->user();

        return view('admin.opportunities.form', $this->formData($actor, new Opportunity, $scope));
    }

    public function store(SaveOpportunityRequest $request, RecordOpportunity $records): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $opportunity = $records->create($actor, $request->validated());

        return redirect()
            ->route('admin.opportunities.show', $opportunity)
            ->with('status', 'Draft saved. Submit it when it is ready for review.');
    }

    public function show(Request $request, Opportunity $opportunity, DistrictScope $scope): View
    {
        $this->visible($request, $opportunity, $scope);
        $opportunity->load([
            'district.region:id,name',
            'sector:id,name',
            'subSector:id,name',
            'enterpriseType:id,name',
            'assignee:id,uuid,name',
            'creator:id,name',
            'documents:id,uuid,opportunity_id,category,original_name,size,created_at',
            'transitions' => fn ($query) => $query->latest('id')->limit(20),
            'transitions.actor:id,name',
        ]);

        /** @var User $actor */
        $actor = $request->user();

        return view('admin.opportunities.show', [
            'opportunity' => $opportunity,
            'actions' => $this->actions($actor, $opportunity),
            'staff' => User::query()->where('user_type', UserType::Staff)->orderBy('name')->get(['id', 'uuid', 'name']),
            'categories' => DocumentCategory::cases(),
        ]);
    }

    public function edit(Request $request, Opportunity $opportunity, DistrictScope $scope): View
    {
        $this->visible($request, $opportunity, $scope);
        abort_unless($opportunity->workflow_state->isEditable(), 403);

        /** @var User $actor */
        $actor = $request->user();

        return view('admin.opportunities.form', $this->formData($actor, $opportunity->load(['district:id,uuid', 'sector:id,uuid', 'subSector:id,uuid', 'enterpriseType:id,uuid']), $scope));
    }

    public function update(SaveOpportunityRequest $request, Opportunity $opportunity, RecordOpportunity $records): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $records->update($actor, $opportunity, $request->validated());

        return redirect()
            ->route('admin.opportunities.show', $opportunity)
            ->with('status', 'Draft updated.');
    }

    public function transition(Request $request, Opportunity $opportunity, TransitionOpportunity $workflow): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in([...array_keys(OpportunityWorkflow::transitions()), 'reassign'])],
            'reason' => ['nullable', 'string', 'max:2000'],
            'assignee_id' => ['nullable', 'uuid'],
        ]);

        /** @var User $actor */
        $actor = $request->user();

        if ($data['action'] === 'reassign') {
            $assignee = User::query()->where('uuid', $data['assignee_id'] ?? null)->first();

            if ($assignee === null) {
                return back()->withErrors(['assignee_id' => 'Choose a staff member.'])->withInput();
            }

            $workflow->reassign($actor, $opportunity, $assignee, (string) ($data['reason'] ?? ''));
        } else {
            $workflow->transition($actor, $opportunity, $data['action'], $data['reason'] ?? null);
        }

        return redirect()
            ->route('admin.opportunities.show', $opportunity)
            ->with('status', 'The opportunity was updated.');
    }

    public function storeDocument(Request $request, Opportunity $opportunity, StoreOpportunityDocument $documents): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'document' => ['required', 'file', 'max:8192'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $documents->store($actor, $opportunity, $data['document'], DocumentCategory::from($data['category']));

        return redirect()
            ->route('admin.opportunities.show', $opportunity)
            ->with('status', 'Document stored.');
    }

    public function download(Request $request, Opportunity $opportunity, OpportunityDocument $document, DistrictScope $scope): StreamedResponse
    {
        $this->visible($request, $opportunity, $scope);
        abort_unless($document->opportunity_id === $opportunity->id, 404);

        return Storage::disk('local')->download($document->storage_path, $document->original_name);
    }

    /**
     * @return list<array{action: string, label: string, reason: bool}>
     */
    private function actions(User $actor, Opportunity $opportunity): array
    {
        $actions = [];

        foreach (OpportunityWorkflow::transitions() as $name => $definition) {
            if (in_array($opportunity->workflow_state, $definition['from'], true) && $actor->can($definition['permission'])) {
                $actions[] = [
                    'action' => $name,
                    'label' => str_replace('_', ' ', ucfirst($name)),
                    'reason' => $definition['reason'],
                ];
            }
        }

        if ($actor->can(AccessPermissions::OPPORTUNITIES_REASSIGN)
            && ! in_array($opportunity->workflow_state, [WorkflowState::Completed, WorkflowState::Cancelled], true)) {
            $actions[] = ['action' => 'reassign', 'label' => 'Reassign', 'reason' => true];
        }

        return $actions;
    }

    private function visible(Request $request, Opportunity $opportunity, DistrictScope $scope): void
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($scope->canSee($actor, $opportunity), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $actor, Opportunity $opportunity, DistrictScope $scope): array
    {
        $sectors = Sector::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'uuid', 'name']);
        $sectorUuids = $sectors->pluck('uuid', 'id');

        return [
            'opportunity' => $opportunity,
            'districts' => $this->districtChoices($actor, $scope),
            'sectors' => $sectors,
            'enterpriseTypes' => EnterpriseType::query()->where('is_active', true)->orderBy('name')->get(['uuid', 'name']),
            'structures' => InvestmentStructure::cases(),
            'catalogue' => [
                'selectedSub' => old('sub_sector_id', $opportunity->subSector?->uuid),
                'subSectors' => SubSector::query()->where('is_active', true)->orderBy('sort_order')->get(['uuid', 'name', 'sector_id'])
                    ->map(fn (SubSector $subSector): array => [
                        'uuid' => $subSector->uuid,
                        'name' => $subSector->name,
                        'sector' => $sectorUuids[$subSector->sector_id] ?? null,
                    ])->values(),
            ],
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Region>
     */
    private function districtChoices(User $actor, DistrictScope $scope)
    {
        $query = Region::query()->with(['districts' => fn ($districts) => $districts->orderBy('name')])->orderBy('name');

        if ($scope->isRestricted($actor)) {
            $query->whereHas('districts', fn ($districts) => $districts->whereIn('id', $actor->districts()->select('districts.id')));
            $query->with(['districts' => fn ($districts) => $districts
                ->whereIn('id', $actor->districts()->select('districts.id'))
                ->orderBy('name')]);
        }

        return $query->get();
    }
}
