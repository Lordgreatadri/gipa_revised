<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Application\Contracts\EmbeddingProvider;
use App\Modules\Assistant\Application\Data\RetrievedChunk;
use App\Modules\Assistant\Domain\Models\AssistantDocument;
use App\Modules\Assistant\Domain\Models\AssistantDocumentChunk;

final class KnowledgeRetriever
{
    public function __construct(private readonly EmbeddingProvider $embeddings) {}

    /**
     * @return list<RetrievedChunk>
     */
    public function retrieve(string $query, string $audience): array
    {
        $vector = $this->embeddings->embed($query);

        if ($this->isZero($vector)) {
            return [];
        }

        $audiences = [AssistantDocument::AUDIENCE_PUBLIC];

        if ($audience === AssistantDocument::AUDIENCE_INVESTOR) {
            $audiences[] = AssistantDocument::AUDIENCE_INVESTOR;
        }

        $rows = AssistantDocumentChunk::query()
            ->join('assistant_documents', 'assistant_documents.id', '=', 'assistant_document_chunks.assistant_document_id')
            ->where('assistant_documents.is_published', true)
            ->whereNull('assistant_documents.deleted_at')
            ->whereIn('assistant_documents.audience', $audiences)
            ->where('assistant_document_chunks.embedding_model', $this->embeddings->model())
            ->get([
                'assistant_document_chunks.content',
                'assistant_document_chunks.embedding',
                'assistant_documents.title as document_title',
                'assistant_documents.slug as document_slug',
                'assistant_documents.category as document_category',
            ]);

        $scored = [];
        $minimum = (float) config('assistant.retrieval.min_score', 0.12);

        foreach ($rows as $row) {
            $embedding = $row->embedding;

            if (! is_array($embedding) || $embedding === []) {
                continue;
            }

            $score = $this->cosine($vector, $embedding);

            if ($score < $minimum) {
                continue;
            }

            $scored[] = new RetrievedChunk(
                documentTitle: (string) $row->document_title,
                documentSlug: (string) $row->document_slug,
                category: (string) $row->document_category,
                content: (string) $row->content,
                score: $score,
            );
        }

        usort($scored, static fn (RetrievedChunk $left, RetrievedChunk $right): int => $right->score <=> $left->score);

        return array_slice($scored, 0, (int) config('assistant.retrieval.max_chunks', 4));
    }

    /**
     * @param  list<float>  $left
     * @param  list<float>  $right
     */
    private function cosine(array $left, array $right): float
    {
        $dot = 0.0;
        $leftMagnitude = 0.0;
        $rightMagnitude = 0.0;
        $length = min(count($left), count($right));

        for ($index = 0; $index < $length; $index++) {
            $dot += $left[$index] * $right[$index];
            $leftMagnitude += $left[$index] * $left[$index];
            $rightMagnitude += $right[$index] * $right[$index];
        }

        if ($leftMagnitude <= 0.0 || $rightMagnitude <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($leftMagnitude) * sqrt($rightMagnitude));
    }

    /**
     * @param  list<float>  $vector
     */
    private function isZero(array $vector): bool
    {
        foreach ($vector as $value) {
            if ($value !== 0.0) {
                return false;
            }
        }

        return true;
    }
}
