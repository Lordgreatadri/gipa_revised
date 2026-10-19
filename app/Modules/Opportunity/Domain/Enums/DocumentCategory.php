<?php

namespace App\Modules\Opportunity\Domain\Enums;

enum DocumentCategory: string
{
    case BusinessPlan = 'business_plan';
    case TechnicalFeasibility = 'technical_feasibility';
    case MarketFeasibility = 'market_feasibility';
    case FundingStructure = 'funding_structure';

    public function label(): string
    {
        return match ($this) {
            self::BusinessPlan => 'Business plan',
            self::TechnicalFeasibility => 'Technical feasibility',
            self::MarketFeasibility => 'Market feasibility',
            self::FundingStructure => 'Funding structure',
        };
    }
}
