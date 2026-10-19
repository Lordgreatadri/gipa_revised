<?php

namespace App\Modules\Assistant\Application\Data;

final class AssistantPrompt
{
    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  list<ToolResult>  $toolResults
     * @param  list<RetrievedChunk>  $chunks
     */
    public function __construct(
        public readonly string $question,
        public readonly array $history,
        public readonly array $toolResults,
        public readonly array $chunks,
        public readonly bool $flagged,
    ) {}

    public function hasContext(): bool
    {
        return $this->toolResults !== [] || $this->chunks !== [];
    }
}
