<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;

/**
 * Names capabilities the client has not approved for this release so the
 * assistant refuses them instead of borrowing them from the demo.
 */
final class UnavailableCapabilityTool extends AbstractTool
{
    public function name(): string
    {
        return 'unavailable_capabilities';
    }

    protected function triggers(): array
    {
        return ['certificate', 'qr code', 'matchmaking', 'readiness', 'kyc', 'identity document'];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        return new ToolResult(
            tool: $this->name(),
            summary: 'Certificates are not issued on this platform, and there is no QR verification. Matchmaking scores, readiness scores, jobs and infrastructure quality are not calculated. Identity documents are not collected yet. The business profile is the current onboarding record.',
            sourceLabel: 'Current platform limits',
            reference: route('platform.guide'),
        );
    }
}
