<?php

namespace App\Modules\Shared\Presentation\Http\Api;

use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Support\Str;

final class OpportunityPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function summary(Opportunity $opportunity): array
    {
        return [
            'id' => $opportunity->uuid,
            'reference' => $opportunity->reference,
            'title' => $opportunity->title,
            'summary' => Str::limit((string) $opportunity->description, 240),
            'sector' => $opportunity->sector?->name,
            'sub_sector' => $opportunity->subSector?->name,
            'enterprise_type' => $opportunity->enterpriseType?->name,
            'region' => $opportunity->district?->region?->name,
            'district' => $opportunity->district?->name,
            'investment_amount' => (string) $opportunity->investment_amount,
            'currency' => $opportunity->currency,
            'project_status' => $opportunity->project_status?->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(Opportunity $opportunity): array
    {
        return self::summary($opportunity) + [
            'description' => $opportunity->description,
            'location_description' => $opportunity->location_description,
            'latitude' => $opportunity->latitude !== null ? (string) $opportunity->latitude : null,
            'longitude' => $opportunity->longitude !== null ? (string) $opportunity->longitude : null,
        ];
    }
}
