<?php

namespace App\Modules\InvestorOnboarding\Domain\Enums;

/**
 * Profile review is a separate gate from e-mail and phone verification.
 * Inquiry and interest wait for approval. Further review states stay unset
 * until the client confirms them.
 */
enum ProfileOnboardingState: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
        };
    }
}
