<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;
use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Opportunity\Domain\Models\Sector;

final class CatalogueTool extends AbstractTool
{
    public function name(): string
    {
        return 'reference_catalogue';
    }

    protected function triggers(): array
    {
        return ['sector', 'district', 'region', 'enterprise'];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        $sectors = Sector::query()->where('is_active', true)->orderBy('name')->pluck('name');
        $regions = Region::query()->count();
        $districts = District::query()->count();

        $sectorLine = $sectors->isEmpty()
            ? 'No active sectors are published yet.'
            : 'Active sectors: '.$sectors->implode(', ').'.';

        return new ToolResult(
            tool: $this->name(),
            summary: 'The reference catalogue has '.$regions.' regions and '.$districts.' districts. '.$sectorLine.' Sector and district names are reference data. They are not a list of investment opportunities.',
            sourceLabel: 'Reference catalogue',
            reference: route('platform.guide'),
            data: ['regions' => $regions, 'districts' => $districts],
        );
    }
}
