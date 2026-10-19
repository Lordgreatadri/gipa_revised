<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;
use App\Modules\PublicPortal\Application\OfficeHours;

final class OfficeHoursTool extends AbstractTool
{
    public function __construct(private readonly OfficeHours $officeHours) {}

    public function name(): string
    {
        return 'office_hours';
    }

    protected function triggers(): array
    {
        return ['office', 'are you open', 'open now', 'closed', 'public holiday', 'working hour', 'what time'];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        $status = $this->officeHours->snapshot();

        return new ToolResult(
            tool: $this->name(),
            summary: 'The office is '.$status['headline'].'. '.$status['detail'].' Desk hours are Monday to Friday, 8:00 am to 5:00 pm, Africa/Accra. The office is closed on Saturday, Sunday, and Ghana public holidays.',
            sourceLabel: 'Office hours',
            reference: route('contact.create'),
        );
    }
}
