<?php

namespace App\Modules\InvestorEngagement\Domain\Enums;

/**
 * Submitted is the only confirmed state. Later review steps stay unset
 * until the client confirms the engagement lifecycle (ADR-0012).
 */
enum EngagementState: string
{
    case Submitted = 'submitted';

    public function label(): string
    {
        return 'Submitted';
    }
}
