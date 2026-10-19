<?php

namespace App\Modules\Assistant\Application\Providers;

use App\Modules\Assistant\Application\Contracts\ChatProvider;
use App\Modules\Assistant\Application\Data\AssistantAnswer;
use App\Modules\Assistant\Application\Data\AssistantPrompt;
use App\Modules\Assistant\Application\Support\Text;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Optional generative driver. It receives the same grounded context as the
 * offline driver and is used only when an API key is configured. Citations
 * still come from the retrieved sources, not from text the model invents.
 */
final class OpenAiChatProvider implements ChatProvider
{
    public function generate(AssistantPrompt $prompt): AssistantAnswer
    {
        $key = (string) config('assistant.providers.openai.api_key');

        if ($key === '') {
            throw new RuntimeException('OpenAI is not configured.');
        }

        $context = $this->context($prompt);
        $response = Http::acceptJson()
            ->withToken($key)
            ->timeout((int) config('assistant.providers.openai.timeout', 20))
            ->post(rtrim((string) config('assistant.providers.openai.base_url'), '/').'/chat/completions', [
                'model' => $this->model(),
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'system', 'content' => (string) config('assistant.guardrails.system_prompt')],
                    ['role' => 'user', 'content' => "Verified context:\n".$context."\n\nQuestion:\n".$prompt->question],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('The assistant provider returned HTTP '.$response->status().'.');
        }

        $content = trim((string) $response->json('choices.0.message.content'));

        if ($content === '') {
            throw new RuntimeException('The assistant provider returned an empty answer.');
        }

        $citations = [];

        foreach ($prompt->toolResults as $result) {
            $citations[] = [
                'type' => 'tool',
                'label' => $result->sourceLabel,
                'category' => null,
                'reference' => $result->reference,
            ];
        }

        foreach ($prompt->chunks as $chunk) {
            $citations[] = [
                'type' => 'knowledge',
                'label' => $chunk->documentTitle,
                'category' => $chunk->category,
                'reference' => $chunk->documentSlug,
            ];
        }

        return new AssistantAnswer(
            content: $content,
            grounded: $prompt->hasContext(),
            provider: $this->name(),
            model: $this->model(),
            citations: $citations,
            toolsUsed: array_values(array_unique(array_map(fn ($result) => $result->tool, $prompt->toolResults))),
            tokensInput: (int) ($response->json('usage.prompt_tokens') ?? Text::estimateTokens($prompt->question)),
            tokensOutput: (int) ($response->json('usage.completion_tokens') ?? Text::estimateTokens($content)),
        );
    }

    public function name(): string
    {
        return 'openai';
    }

    public function model(): string
    {
        return (string) config('assistant.providers.openai.chat_model', 'gpt-4o-mini');
    }

    private function context(AssistantPrompt $prompt): string
    {
        $parts = [];

        foreach ($prompt->toolResults as $result) {
            $parts[] = $result->summary;
        }

        foreach ($prompt->chunks as $chunk) {
            $parts[] = $chunk->documentTitle."\n".$chunk->content;
        }

        return $parts === []
            ? 'No verified context was retrieved.'
            : implode("\n\n", $parts);
    }
}
