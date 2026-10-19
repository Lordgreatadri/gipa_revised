<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Application\Contracts\AssistantTool;
use App\Modules\Assistant\Application\Contracts\ChatProvider;
use App\Modules\Assistant\Application\Data\AssistantAnswer;
use App\Modules\Assistant\Application\Data\AssistantPrompt;
use App\Modules\Assistant\Application\Data\ToolResult;
use App\Modules\Assistant\Application\Support\Text;
use App\Modules\Assistant\Domain\Models\AssistantConversation;
use App\Modules\Assistant\Domain\Models\AssistantMessage;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class AssistantService
{
    /** @var list<AssistantTool> */
    private array $tools;

    /**
     * @param  iterable<AssistantTool>  $tools
     */
    public function __construct(
        private readonly ChatProvider $chat,
        private readonly KnowledgeRetriever $retriever,
        private readonly Guardrails $guardrails,
        iterable $tools = [],
    ) {
        $this->tools = array_values(is_array($tools) ? $tools : iterator_to_array($tools));
    }

    /**
     * @param  array{session_token?: string|null, ip_hash?: string|null}  $meta
     * @return array{conversation: AssistantConversation, message: AssistantMessage}
     */
    public function ask(string $message, ?AssistantConversation $conversation, ?User $user, array $meta = []): array
    {
        $clean = $this->guardrails->sanitize($message);
        $flagged = $this->guardrails->detectInjection($clean);
        $audience = AssistantAudience::for($user);
        $conversation = $this->conversation($conversation, $user, $audience, $meta);
        $history = $this->history($conversation);
        $retrievalText = $flagged ? $clean : $this->retrievalText($clean, $history);

        $userMessage = $conversation->messages()->make();
        $userMessage->forceFill([
            'role' => AssistantMessage::ROLE_USER,
            'content' => $clean,
            'flagged' => $flagged,
        ])->save();

        $prompt = new AssistantPrompt(
            question: $clean,
            history: $history,
            toolResults: $flagged ? [] : $this->tools($clean, $audience),
            chunks: $flagged ? [] : $this->retriever->retrieve($retrievalText, $audience),
            flagged: $flagged,
        );

        $started = microtime(true);
        $answer = $this->generate($prompt);
        $latency = (int) round((microtime(true) - $started) * 1000);

        $reply = $conversation->messages()->make();
        $reply->forceFill([
            'role' => AssistantMessage::ROLE_ASSISTANT,
            'content' => $answer->content,
            'citations' => $answer->citations,
            'tools_used' => $answer->toolsUsed,
            'provider' => $answer->provider,
            'model' => $answer->model,
            'tokens_input' => $answer->tokensInput,
            'tokens_output' => $answer->tokensOutput,
            'latency_ms' => $latency,
            'was_grounded' => $answer->grounded,
            'flagged' => $flagged,
        ])->save();

        $conversation->forceFill([
            'last_activity_at' => now(),
            'title' => $conversation->title ?: Str::limit($clean, 80, ''),
        ])->save();

        Log::info('assistant.turn', [
            'conversation' => $conversation->uuid,
            'provider' => $answer->provider,
            'grounded' => $answer->grounded,
            'flagged' => $flagged,
            'tools' => $answer->toolsUsed,
            'latency_ms' => $latency,
        ]);

        return ['conversation' => $conversation, 'message' => $reply];
    }

    /**
     * @return list<ToolResult>
     */
    private function tools(string $question, string $audience): array
    {
        $results = [];

        foreach ($this->tools as $tool) {
            if (! $tool->matches($question)) {
                continue;
            }

            try {
                $result = $tool->handle($question, $audience);
            } catch (Throwable $exception) {
                Log::warning('assistant.tool_failed', [
                    'tool' => $tool->name(),
                    'error' => $exception->getMessage(),
                ]);

                continue;
            }

            if ($result instanceof ToolResult) {
                $results[] = $result;
            }
        }

        return $results;
    }

    private function generate(AssistantPrompt $prompt): AssistantAnswer
    {
        try {
            return $this->chat->generate($prompt);
        } catch (Throwable $exception) {
            Log::warning('assistant.generation_failed', ['error' => $exception->getMessage()]);

            return new AssistantAnswer(
                content: (string) config('assistant.guardrails.refusal_message'),
                grounded: false,
                provider: $this->chat->name(),
                model: $this->chat->model(),
                tokensInput: Text::estimateTokens($prompt->question),
            );
        }
    }

    /**
     * @param  array{session_token?: string|null, ip_hash?: string|null}  $meta
     */
    private function conversation(?AssistantConversation $conversation, ?User $user, string $audience, array $meta): AssistantConversation
    {
        if ($conversation !== null) {
            return $conversation;
        }

        $created = new AssistantConversation;
        $created->forceFill([
            'user_id' => $user?->id,
            'session_token' => $meta['session_token'] ?? null,
            'audience' => $audience,
            'ip_hash' => $meta['ip_hash'] ?? null,
            'last_activity_at' => now(),
        ])->save();

        return $created;
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function history(AssistantConversation $conversation): array
    {
        $limit = (int) config('assistant.conversation.max_history_messages', 8);

        return $conversation->messages()
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['role', 'content', 'flagged'])
            ->reverse()
            ->map(fn (AssistantMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
                'flagged' => (bool) $message->flagged,
            ])
            ->values()
            ->all();
    }

    /**
     * Tools always see the current question. Retrieval includes the previous
     * question only for a short follow-up such as "what about the phone?".
     *
     * @param  list<array{role: string, content: string, flagged?: bool}>  $history
     */
    private function retrievalText(string $question, array $history): string
    {
        if (! $this->isFollowUp($question)) {
            return $question;
        }

        foreach (array_reverse($history) as $message) {
            if ($message['role'] === AssistantMessage::ROLE_USER && empty($message['flagged'])) {
                return $message['content'].' '.$question;
            }
        }

        return $question;
    }

    private function isFollowUp(string $question): bool
    {
        return (bool) preg_match(
            '/^(and|also|what about|how about|tell me more|more about|it|that|this|they|those)\b/iu',
            $question,
        );
    }
}
