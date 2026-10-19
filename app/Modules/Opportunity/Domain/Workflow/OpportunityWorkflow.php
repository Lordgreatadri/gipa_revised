<?php

namespace App\Modules\Opportunity\Domain\Workflow;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;

/**
 * Explicit opportunity transitions. Editing a draft does not grant any of
 * these. Rejection returns the record to draft so the originating officer
 * can correct it and submit again; the baseline lifecycle has no separate
 * rejected state.
 */
final class OpportunityWorkflow
{
    /**
     * @return array<string, array{from: list<WorkflowState>, to: WorkflowState, permission: string, reason: bool}>
     */
    public static function transitions(): array
    {
        return [
            'submit' => [
                'from' => [WorkflowState::Draft],
                'to' => WorkflowState::PendingApproval,
                'permission' => AccessPermissions::OPPORTUNITIES_SUBMIT,
                'reason' => false,
            ],
            'approve' => [
                'from' => [WorkflowState::PendingApproval],
                'to' => WorkflowState::Approved,
                'permission' => AccessPermissions::OPPORTUNITIES_APPROVE,
                'reason' => false,
            ],
            'reject' => [
                'from' => [WorkflowState::PendingApproval],
                'to' => WorkflowState::Draft,
                'permission' => AccessPermissions::OPPORTUNITIES_REJECT,
                'reason' => true,
            ],
            'activate' => [
                'from' => [WorkflowState::Approved],
                'to' => WorkflowState::Active,
                'permission' => AccessPermissions::OPPORTUNITIES_ACTIVATE,
                'reason' => false,
            ],
            'complete' => [
                'from' => [WorkflowState::Active],
                'to' => WorkflowState::Completed,
                'permission' => AccessPermissions::OPPORTUNITIES_COMPLETE,
                'reason' => false,
            ],
            'cancel' => [
                'from' => [
                    WorkflowState::Draft,
                    WorkflowState::PendingApproval,
                    WorkflowState::Approved,
                    WorkflowState::Active,
                ],
                'to' => WorkflowState::Cancelled,
                'permission' => AccessPermissions::OPPORTUNITIES_CANCEL,
                'reason' => true,
            ],
        ];
    }

    /**
     * @return array{from: list<WorkflowState>, to: WorkflowState, permission: string, reason: bool}|null
     */
    public static function available(string $action, WorkflowState $state): ?array
    {
        $transition = self::transitions()[$action] ?? null;

        if ($transition === null || ! in_array($state, $transition['from'], true)) {
            return null;
        }

        return $transition;
    }
}
