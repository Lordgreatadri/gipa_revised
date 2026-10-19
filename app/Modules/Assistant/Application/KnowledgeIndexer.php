<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Application\Contracts\EmbeddingProvider;
use App\Modules\Assistant\Application\Support\Text;
use App\Modules\Assistant\Domain\Models\AssistantDocument;

final class KnowledgeIndexer
{
    public function __construct(private readonly EmbeddingProvider $embeddings) {}

    public function reindex(AssistantDocument $document): void
    {
        $document->chunks()->delete();
        $checksum = hash('sha256', (string) $document->body);

        if (! $document->is_published || trim((string) $document->body) === '') {
            $document->forceFill([
                'checksum' => $checksum,
                'indexed_at' => null,
            ])->save();

            return;
        }

        foreach ($this->chunks((string) $document->body) as $ordinal => $content) {
            $chunk = $document->chunks()->make();
            $chunk->forceFill([
                'ordinal' => $ordinal,
                'content' => $content,
                'token_estimate' => Text::estimateTokens($content),
                'embedding' => $this->embeddings->embed($content),
                'embedding_model' => $this->embeddings->model(),
            ])->save();
        }

        $document->forceFill([
            'checksum' => $checksum,
            'indexed_at' => now(),
        ])->save();
    }

    public function reindexAll(): void
    {
        AssistantDocument::query()->orderBy('id')->each(function (AssistantDocument $document): void {
            $this->reindex($document);
        });
    }

    /**
     * @return list<string>
     */
    private function chunks(string $body): array
    {
        $size = max(200, (int) config('assistant.retrieval.chunk_size', 900));
        $paragraphs = preg_split("/\n{2,}/", trim($body)) ?: [];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim((string) $paragraph);

            if ($paragraph === '') {
                continue;
            }

            $candidate = $current === '' ? $paragraph : $current."\n\n".$paragraph;

            if ($current !== '' && mb_strlen($candidate) > $size) {
                $chunks[] = $current;
                $current = $paragraph;

                continue;
            }

            $current = $candidate;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks === [] ? [trim($body)] : $chunks;
    }
}
