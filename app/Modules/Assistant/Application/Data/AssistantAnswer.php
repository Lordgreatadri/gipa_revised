<?php

namespace App\Modules\Assistant\Application\Data;

final class AssistantAnswer
{
    /**
     * @param  list<array{type: string, label: string, category: string|null, reference: string|null}>  $citations
     * @param  list<string>  $toolsUsed
     */
    public function __construct(
        public readonly string $content,
        public readonly bool $grounded,
        public readonly string $provider,
        public readonly string $model,
        public readonly array $citations = [],
        public readonly array $toolsUsed = [],
        public readonly int $tokensInput = 0,
        public readonly int $tokensOutput = 0,
    ) {}
}
