<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;
use App\Modules\Assistant\Domain\Models\AssistantDocument;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;

/**
 * Opportunity titles stay behind the same gate as the investor workspace.
 * A guest receives the access rule and never a record title or contact.
 */
final class ActiveOpportunityTool extends AbstractTool
{
    public function name(): string
    {
        return 'active_opportunities';
    }

    protected function triggers(): array
    {
        return ['opportunit', 'invest in', 'what can i invest', 'available to invest'];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        if ($audience !== AssistantDocument::AUDIENCE_INVESTOR) {
            return new ToolResult(
                tool: $this->name(),
                summary: 'Approved and active opportunities are not listed on the public site or in this public conversation. Register, verify your e-mail and phone, and save a business profile. The Opportunities page in the investor workspace then shows only approved and active records. Drafts and records still under review are not listed, and a record does not show an officer phone number or e-mail.',
                sourceLabel: 'Opportunity access rule',
                reference: route('platform.guide'),
            );
        }

        $query = Opportunity::query()
            ->investorVisible()
            ->select(['id', 'uuid', 'title', 'sector_id', 'district_id', 'investment_amount', 'currency'])
            ->with([
                'sector:id,name',
                'district:id,name,region_id',
                'district.region:id,name',
            ])
            ->latest('id');

        $sector = $this->sector($question);

        if ($sector !== null) {
            $query->where('sector_id', $sector->id);
        }

        $records = $query->limit(5)->get();

        if ($records->isEmpty()) {
            return new ToolResult(
                tool: $this->name(),
                summary: 'There are no approved and active opportunities to list yet.',
                sourceLabel: 'Active opportunities',
                reference: route('investor.opportunities'),
            );
        }

        $lines = $records->map(function (Opportunity $opportunity): string {
            $place = trim(($opportunity->district?->name ?? '').', '.($opportunity->district?->region?->name ?? ''), ', ');

            return '• '.$opportunity->title
                .' — '.($opportunity->sector?->name ?? 'Sector not set')
                .' — '.$place
                .' — '.$opportunity->currency.' '.number_format((float) $opportunity->investment_amount, 0);
        })->implode("\n");

        $intro = $sector === null
            ? 'These are approved and active opportunities:'
            : 'These are approved and active opportunities in '.$sector->name.':';

        return new ToolResult(
            tool: $this->name(),
            summary: $intro."\n".$lines."\n\nOpen Opportunities in the investor workspace to filter the full active list. Officer contact details are not included.",
            sourceLabel: 'Active opportunities',
            reference: route('investor.opportunities'),
            data: ['count' => $records->count()],
        );
    }

    private function sector(string $question): ?Sector
    {
        $haystack = mb_strtolower($question);

        return Sector::query()
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->first(fn (Sector $sector): bool => str_contains($haystack, mb_strtolower($sector->name)));
    }
}
