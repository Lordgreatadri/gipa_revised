<?php

namespace App\Modules\Opportunity\Domain\Enums;

enum InvestmentStructure: string
{
    case LocalEquity = 'local_equity';
    case ForeignEquity = 'foreign_equity';
    case LocalLoan = 'local_loan';
    case ForeignLoan = 'foreign_loan';

    public function label(): string
    {
        return match ($this) {
            self::LocalEquity => 'Local equity',
            self::ForeignEquity => 'Foreign equity',
            self::LocalLoan => 'Local loan',
            self::ForeignLoan => 'Foreign loan',
        };
    }
}
