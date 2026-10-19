<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\ProjectStatus;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\OpportunityTransition;
use App\Modules\Opportunity\Domain\OpportunityAuditActions;
use App\Modules\Opportunity\Domain\Workflow\OpportunityWorkflow;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionOpportunity
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly DistrictScope $scope,
    ) {}

    public function transition(User $actor, Opportunity $opportunity, string $action, ?string $reason = null): Opportunity
    {
        if (! $this->scope->canSee($actor, $opportunity)) {
            throw new AuthorizationException;
        }

        $definition = OpportunityWorkflow::available($action, $opportunity->workflow_state);

        if ($definition === null || ! $actor->can($definition['permission'])) {
            throw new AuthorizationException;
        }

        $reason = $this->reason($definition['reason'], $reason);
        $from = $opportunity->workflow_state;
        $to = $definition['to'];

        return DB::transaction(function () use ($actor, $opportunity, $action, $reason, $from, $to): Opportunity {
            $opportunity->workflow_state = $to;
            $opportunity->project_status = ProjectStatus::forWorkflow($to);
            $this->stamp($opportunity, $to);
            $opportunity->save();

            $this->history($opportunity, $actor, $action, $from, $to, $reason, null);

            $this->audit->record(
                AuditEntry::for(OpportunityAuditActions::TRANSITIONED, $opportunity)
                    ->causedBy($actor)
                    ->withReason($reason)
                    ->withMetadata([
                        'reference' => $opportunity->reference,
                        'action' => $action,
                        'from' => $from->value,
                        'to' => $to->value,
                    ])
            );

            return $opportunity;
        });
    }

    public function reassign(User $actor, Opportunity $opportunity, User $assignee, string $reason): Opportunity
    {
        if (! $actor->can(AccessPermissions::OPPORTUNITIES_REASSIGN) || ! $this->scope->canSee($actor, $opportunity)) {
            throw new AuthorizationException;
        }

        if (in_array($opportunity->workflow_state, [WorkflowState::Completed, WorkflowState::Cancelled], true)) {
            throw ValidationException::withMessages([
                'assignee_id' => 'A completed or cancelled opportunity cannot be reassigned.',
            ]);
        }

        if (! $assignee->isStaff()) {
            throw ValidationException::withMessages([
                'assignee_id' => 'Choose a staff member.',
            ]);
        }

        if ($this->scope->isRestricted($assignee) && ! $assignee->districts()->whereKey($opportunity->district_id)->exists()) {
            throw ValidationException::withMessages([
                'assignee_id' => 'That officer is not assigned to this district.',
            ]);
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required.',
            ]);
        }

        return DB::transaction(function () use ($actor, $opportunity, $assignee, $reason): Opportunity {
            $previous = $opportunity->assigned_to;
            $opportunity->assigned_to = $assignee->id;
            $opportunity->save();

            $this->history($opportunity, $actor, 'reassign', $opportunity->workflow_state, $opportunity->workflow_state, $reason, $assignee->id);

            $this->audit->record(
                AuditEntry::for(OpportunityAuditActions::REASSIGNED, $opportunity)
                    ->causedBy($actor)
                    ->withReason($reason)
                    ->withMetadata([
                        'reference' => $opportunity->reference,
                        'from_user_id' => $previous,
                        'to_user_id' => $assignee->id,
                    ])
            );

            return $opportunity;
        });
    }

    private function reason(bool $required, ?string $reason): ?string
    {
        $reason = trim((string) $reason);

        if ($required && $reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required for this decision.',
            ]);
        }

        return $reason === '' ? null : $reason;
    }

    private function stamp(Opportunity $opportunity, WorkflowState $state): void
    {
        $now = now();

        match ($state) {
            WorkflowState::PendingApproval => $opportunity->submitted_at = $now,
            WorkflowState::Approved => $opportunity->approved_at = $now,
            WorkflowState::Active => $opportunity->activated_at = $now,
            WorkflowState::Completed => $opportunity->completed_at = $now,
            WorkflowState::Cancelled => $opportunity->cancelled_at = $now,
            WorkflowState::Draft => null,
        };
    }

    private function history(
        Opportunity $opportunity,
        User $actor,
        string $action,
        WorkflowState $from,
        WorkflowState $to,
        ?string $reason,
        ?int $assigneeId,
    ): void {
        OpportunityTransition::query()->create([
            'opportunity_id' => $opportunity->id,
            'from_state' => $from,
            'to_state' => $to,
            'action' => $action,
            'actor_id' => $actor->id,
            'assigned_to' => $assigneeId,
            'reason' => $reason,
        ]);
    }
}
