<?php

namespace App\Modules\Opportunity\Domain\Enums;

/**
 * Opportunity approval lifecycle. Project status is stored separately and
 * is set from this state when a record is created or moved.
 */
enum WorkflowState: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Client-INV-04: investors see a record only after it has been approved
     * and then activated. Active is that state.
     */
    public function isInvestorVisible(): bool
    {
        return $this === self::Active;
    }
}
