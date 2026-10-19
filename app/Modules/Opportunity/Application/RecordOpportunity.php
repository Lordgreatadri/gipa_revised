<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\ProjectStatus;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use App\Modules\Opportunity\Domain\OpportunityAuditActions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RecordOpportunity
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly DistrictScope $scope,
        private readonly OpportunityLocation $locations,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $actor, array $attributes): Opportunity
    {
        if (! $actor->can(AccessPermissions::OPPORTUNITIES_CREATE)) {
            throw new AuthorizationException;
        }

        $district = $this->district($attributes['district_id'] ?? null);
        $this->scope->assertMayUse($actor, $district);
        [$sector, $subSector, $enterprise] = $this->catalogue($attributes);

        return DB::transaction(function () use ($actor, $attributes, $district, $sector, $subSector, $enterprise): Opportunity {
            $opportunity = new Opportunity($this->content($attributes));
            $opportunity->district()->associate($district);
            $opportunity->sector()->associate($sector);
            $opportunity->subSector()->associate($subSector);
            $opportunity->enterpriseType()->associate($enterprise);
            $opportunity->workflow_state = WorkflowState::Draft;
            $opportunity->project_status = ProjectStatus::forWorkflow(WorkflowState::Draft);
            $opportunity->reference = $this->reference($district);
            $opportunity->created_by = $actor->id;
            $opportunity->assigned_to = $actor->id;
            $opportunity->save();
            $this->locations->sync($opportunity);

            $this->audit->record(
                AuditEntry::for(OpportunityAuditActions::CREATED, $opportunity)
                    ->causedBy($actor)
                    ->withMetadata([
                        'reference' => $opportunity->reference,
                        'district' => $district->code,
                        'state' => WorkflowState::Draft->value,
                    ])
            );

            return $opportunity;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $actor, Opportunity $opportunity, array $attributes): Opportunity
    {
        if (! $actor->can(AccessPermissions::OPPORTUNITIES_UPDATE) || ! $this->scope->canSee($actor, $opportunity)) {
            throw new AuthorizationException;
        }

        if (! $opportunity->workflow_state->isEditable()) {
            throw ValidationException::withMessages([
                'title' => 'Only a draft can be edited. Rejected records return to draft before they can be changed.',
            ]);
        }

        $district = $this->district($attributes['district_id'] ?? null);
        $this->scope->assertMayUse($actor, $district);
        [$sector, $subSector, $enterprise] = $this->catalogue($attributes);

        return DB::transaction(function () use ($actor, $opportunity, $attributes, $district, $sector, $subSector, $enterprise): Opportunity {
            $before = $opportunity->workflow_state->value;
            $opportunity->fill($this->content($attributes));
            $opportunity->district()->associate($district);
            $opportunity->sector()->associate($sector);
            $opportunity->subSector()->associate($subSector);
            $opportunity->enterpriseType()->associate($enterprise);
            $opportunity->save();
            $this->locations->sync($opportunity);

            $this->audit->record(
                AuditEntry::for(OpportunityAuditActions::UPDATED, $opportunity)
                    ->causedBy($actor)
                    ->withMetadata([
                        'reference' => $opportunity->reference,
                        'district' => $district->code,
                        'state' => $before,
                    ])
            );

            return $opportunity;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function content(array $attributes): array
    {
        return collect($attributes)->only((new Opportunity)->getFillable())->all();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: Sector, 1: SubSector, 2: EnterpriseType}
     */
    private function catalogue(array $attributes): array
    {
        $sector = Sector::query()->where('uuid', $attributes['sector_id'] ?? null)->where('is_active', true)->first();
        $subSector = SubSector::query()->where('uuid', $attributes['sub_sector_id'] ?? null)->where('is_active', true)->first();
        $enterprise = EnterpriseType::query()->where('uuid', $attributes['enterprise_type_id'] ?? null)->where('is_active', true)->first();

        $errors = [];

        if ($sector === null) {
            $errors['sector_id'] = 'Choose a sector.';
        }

        if ($enterprise === null) {
            $errors['enterprise_type_id'] = 'Choose an enterprise type.';
        }

        if ($subSector === null || ($sector !== null && $subSector->sector_id !== $sector->id)) {
            $errors['sub_sector_id'] = 'Choose a sub-sector that belongs to the selected sector.';
        }

        if ($errors !== [] || ! $sector instanceof Sector || ! $subSector instanceof SubSector || ! $enterprise instanceof EnterpriseType) {
            throw ValidationException::withMessages($errors !== [] ? $errors : [
                'sector_id' => 'Choose a sector.',
            ]);
        }

        return [$sector, $subSector, $enterprise];
    }

    private function district(mixed $uuid): District
    {
        $district = District::query()->where('uuid', $uuid)->first();

        if ($district === null) {
            throw ValidationException::withMessages([
                'district_id' => 'Choose a district.',
            ]);
        }

        return $district;
    }

    private function reference(District $district): string
    {
        $prefix = 'IOMP-'.now()->year.'-'.$district->code.'-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = $prefix.strtoupper(Str::random(6));

            if (! Opportunity::query()->where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw ValidationException::withMessages([
            'title' => 'A reference could not be reserved. Submit the record again.',
        ]);
    }
}
