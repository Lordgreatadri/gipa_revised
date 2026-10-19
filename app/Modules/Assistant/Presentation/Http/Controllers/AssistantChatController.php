<?php

namespace App\Modules\Assistant\Presentation\Http\Controllers;

use App\Modules\Assistant\Application\AssistantService;
use App\Modules\Assistant\Domain\Models\AssistantConversation;
use App\Modules\Assistant\Presentation\Http\Requests\AssistantChatRequest;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\JsonResponse;

class AssistantChatController
{
    public function __construct(private readonly AssistantService $assistant) {}

    public function store(AssistantChatRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $conversation = $this->conversation($request);

        ['conversation' => $conversation, 'message' => $message] = $this->assistant->ask(
            message: (string) $request->string('message'),
            conversation: $conversation,
            user: $user,
            meta: [
                'session_token' => $this->sessionToken($request),
                'ip_hash' => hash('sha256', (string) $request->ip()),
            ],
        );

        return response()->json([
            'conversation' => $conversation->uuid,
            'reply' => [
                'content' => $message->content,
                'citations' => $message->citations ?? [],
                'tools' => $message->tools_used ?? [],
                'grounded' => (bool) $message->was_grounded,
            ],
        ]);
    }

    private function conversation(AssistantChatRequest $request): ?AssistantConversation
    {
        $uuid = (string) $request->input('conversation', '');

        if ($uuid === '') {
            return null;
        }

        $conversation = AssistantConversation::query()->where('uuid', $uuid)->first();

        if ($conversation === null) {
            return null;
        }

        $user = $request->user();

        if ($conversation->user_id !== null) {
            return $user !== null && $conversation->user_id === $user->id ? $conversation : null;
        }

        $token = $this->sessionToken($request);

        return $user === null && $token !== '' && hash_equals((string) $conversation->session_token, $token)
            ? $conversation
            : null;
    }

    private function sessionToken(AssistantChatRequest $request): string
    {
        $id = (string) $request->session()->getId();

        return $id === '' ? '' : hash('sha256', $id);
    }
}
