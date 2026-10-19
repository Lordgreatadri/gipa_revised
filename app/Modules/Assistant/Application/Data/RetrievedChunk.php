<?php

namespace App\Modules\Assistant\Application\Data;

final class RetrievedChunk
{
    public function __construct(
        public readonly string $documentTitle,
        public readonly string $documentSlug,
        public readonly string $category,
        public readonly string $content,
        public readonly float $score,
    ) {}
}
