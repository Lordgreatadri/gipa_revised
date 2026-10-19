<?php

namespace App\Modules\Assistant\Application\Contracts;

use App\Modules\Assistant\Application\Data\ToolResult;

interface AssistantTool
{
    public function name(): string;

    public function matches(string $question): bool;

    public function handle(string $question, string $audience): ?ToolResult;
}
