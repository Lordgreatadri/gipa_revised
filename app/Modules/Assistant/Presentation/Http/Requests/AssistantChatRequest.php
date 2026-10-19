<?php

namespace App\Modules\Assistant\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistantChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:'.(int) config('assistant.guardrails.max_question_length', 1000)],
            'conversation' => ['nullable', 'uuid'],
        ];
    }
}
