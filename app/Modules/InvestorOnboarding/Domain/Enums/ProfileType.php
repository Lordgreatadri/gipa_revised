<?php

namespace App\Modules\InvestorOnboarding\Domain\Enums;

enum ProfileType: string
{
    case Individual = 'individual';
    case Organization = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Organization => 'Organization',
        };
    }
}
