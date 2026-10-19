<?php

namespace App\Modules\Assistant\Application\Providers;

use App\Modules\Assistant\Application\Contracts\ChatProvider;
use App\Modules\Assistant\Application\Data\AssistantAnswer;
use App\Modules\Assistant\Application\Data\AssistantPrompt;
use App\Modules\Assistant\Application\Support\Text;

/**
 * Composes an answer only from tool results and retrieved chunks.
 * It does not paraphrase, so it cannot invent a certificate, a fee, or a record.
 */
final class OfflineChatProvider implements ChatProvider
{
    private const MAX_EXCERPT = 700;

    public function generate(AssistantPrompt $prompt): AssistantAnswer
    {
        if ($prompt->flagged && ! $prompt->hasContext()) {
            return $this->answer((string) config('assistant.guardrails.injection_deflection'), $prompt, false);
        }

        if (! $prompt->hasContext()) {
            return $this->answer((string) config('assistant.guardrails.refusal_message'), $prompt, false);
        }

        $segments = [];
        $citations = [];
        $tools = [];

        foreach ($prompt->toolResults as $result) {
            $segments[] = $result->summary;
            $tools[] = $result->tool;
            $citations[] = [
                'type' => 'tool',
                'label' => $result->sourceLabel,
                'category' => null,
                'reference' => $result->reference,
            ];
        }

        $excerpts = [];

        foreach ($prompt->chunks as $chunk) {
            $excerpts[] = $this->excerpt($chunk->content);
            $citations[] = [
                'type' => 'knowledge',
                'label' => $chunk->documentTitle,
                'category' => $chunk->category,
                'reference' => $chunk->documentSlug,
            ];
        }

        if ($segments !== []) {
            $body = implode("\n\n", $segments);
            $citations = array_values(array_filter(
                $citations,
                fn (array $citation): bool => $citation['type'] === 'tool',
            ));
        } else {
            $body = implode("\n\n", array_slice($excerpts, 0, 2));
            $citations = array_slice($citations, 0, 2);
        }

        return $this->answer($body, $prompt, true, $this->uniqueCitations($citations), array_values(array_unique($tools)));
    }

    public function name(): string
    {
        return 'offline';
    }

    public function model(): string
    {
        return 'offline-rag';
    }

    /**
     * @param  list<array{type: string, label: string, category: string|null, reference: string|null}>  $citations
     * @param  list<string>  $tools
     */
    private function answer(string $content, AssistantPrompt $prompt, bool $grounded, array $citations = [], array $tools = []): AssistantAnswer
    {
        return new AssistantAnswer(
            content: $content,
            grounded: $grounded,
            provider: $this->name(),
            model: $this->model(),
            citations: $citations,
            toolsUsed: $tools,
            tokensInput: Text::estimateTokens($prompt->question),
            tokensOutput: Text::estimateTokens($content),
        );
    }

    private function excerpt(string $content): string
    {
        $content = Text::clean($content);

        if (mb_strlen($content) <= self::MAX_EXCERPT) {
            return $content;
        }

        return rtrim(mb_substr($content, 0, self::MAX_EXCERPT)).'…';
    }

    /**
     * @param  list<array{type: string, label: string, category: string|null, reference: string|null}>  $citations
     * @return list<array{type: string, label: string, category: string|null, reference: string|null}>
     */
    private function uniqueCitations(array $citations): array
    {
        $seen = [];
        $unique = [];

        foreach ($citations as $citation) {
            $key = $citation['type'].'|'.$citation['label'];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $citation;
        }

        return $unique;
    }
}
