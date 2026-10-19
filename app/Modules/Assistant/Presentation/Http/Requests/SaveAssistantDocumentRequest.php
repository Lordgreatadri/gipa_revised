<?php

namespace App\Modules\Assistant\Presentation\Http\Requests;

use App\Modules\Assistant\Domain\Models\AssistantDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAssistantDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assistant.knowledge.manage') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', Rule::in(array_keys(AssistantDocument::CATEGORIES))],
            'audience' => ['required', Rule::in([AssistantDocument::AUDIENCE_PUBLIC, AssistantDocument::AUDIENCE_INVESTOR])],
            'summary' => ['nullable', 'string', 'max:280'],
            'body' => ['required', 'string', 'min:40', 'max:20000'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
