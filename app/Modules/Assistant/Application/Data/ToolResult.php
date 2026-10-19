<?php

namespace App\Modules\Assistant\Application\Data;

final class ToolResult
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $tool,
        public readonly string $summary,
        public readonly string $sourceLabel,
        public readonly ?string $reference = null,
        public readonly array $data = [],
    ) {}
}
