<?php

namespace App\Modules\Opportunity\Domain\Enums;

enum ProjectStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not started',
            self::InProgress => 'In progress',
            self::OnHold => 'On hold',
        };
    }

    /**
     * Project status follows the workflow. It is not chosen on the entry form.
     * Draft, pending and approved stay not started. Active and completed are
     * in progress. Cancellation places the project on hold.
     */
    public static function forWorkflow(WorkflowState $state): self
    {
        return match ($state) {
            WorkflowState::Draft, WorkflowState::PendingApproval, WorkflowState::Approved => self::NotStarted,
            WorkflowState::Active, WorkflowState::Completed => self::InProgress,
            WorkflowState::Cancelled => self::OnHold,
        };
    }
}
