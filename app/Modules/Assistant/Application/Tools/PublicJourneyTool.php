<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;

final class PublicJourneyTool extends AbstractTool
{
    public function name(): string
    {
        return 'investor_journey';
    }

    protected function triggers(): array
    {
        return [
            'register', 'registration', 'sign up', 'onboard', 'verify', 'verification',
            'phone', 'e-mail', 'email', 'business profile', 'investor account',
        ];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        return new ToolResult(
            tool: $this->name(),
            summary: 'An investor registers on this platform, verifies the e-mail address, then confirms the mobile number with a one-time code sent by text message. The next required step is a business profile. The dashboard, opportunities, investment map, engagement, account and notifications stay closed until that profile is saved. An inquiry or an expression of interest also waits until GIPA approves the profile. Identity documents are not collected yet. Changing the e-mail or the phone starts verification again.',
            sourceLabel: 'Investor access journey',
            reference: route('platform.guide'),
        );
    }
}
