<?php

namespace App\Modules\InvestorEngagement\Domain\Enums;

enum EngagementKind: string
{
    case Inquiry = 'inquiry';
    case Interest = 'interest';

    public function label(): string
    {
        return match ($this) {
            self::Inquiry => 'Inquiry',
            self::Interest => 'Interest',
        };
    }
}
