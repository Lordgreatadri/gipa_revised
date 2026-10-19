<?php

namespace App\Modules\Assistant\Application\Providers;

use App\Modules\Assistant\Application\Contracts\EmbeddingProvider;
use App\Modules\Assistant\Application\Support\Text;

/**
 * Hashed bag-of-words vector. It is deterministic, needs no network, and
 * ranks a chunk by overlap with the question. OpenAI embeddings can replace
 * this provider later; stored chunks must be reindexed after that change.
 */
final class OfflineEmbeddingProvider implements EmbeddingProvider
{
    public function embed(string $text): array
    {
        $dimensions = max(32, (int) config('assistant.embedding.dimensions', 256));
        $vector = array_fill(0, $dimensions, 0.0);

        foreach (Text::tokens($text) as $token) {
            $vector[crc32($token) % $dimensions] += 1.0;
        }

        $magnitude = sqrt(array_sum(array_map(static fn (float $value): float => $value * $value, $vector)));

        if ($magnitude <= 0.0) {
            return $vector;
        }

        return array_map(static fn (float $value): float => $value / $magnitude, $vector);
    }

    public function model(): string
    {
        return 'offline-bow-'.(int) config('assistant.embedding.dimensions', 256);
    }
}
