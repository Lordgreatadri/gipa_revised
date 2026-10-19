@php
    $editing = $document->exists;
@endphp

<x-layouts.workspace :title="$editing ? __('Edit assistant document') : __('Add assistant document')">
    <a href="{{ route('admin.assistant.knowledge.index') }}" class="link text-sm">{{ __('Back to assistant knowledge') }}</a>
    <h2 class="font-display mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ $editing ? $document->title : __('New document') }}</h2>

    <form method="POST" action="{{ $editing ? route('admin.assistant.knowledge.update', $document) : route('admin.assistant.knowledge.store') }}" class="mt-6 max-w-3xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div>
            <x-ui.label for="title" required>{{ __('Title') }}</x-ui.label>
            <x-ui.input id="title" name="title" :value="old('title', $document->title)" required maxlength="160" />
            <x-ui.input-error for="title" />
        </div>
        <div>
            <x-ui.label for="category" required>{{ __('Category') }}</x-ui.label>
            <select id="category" name="category" class="form-control mt-1.5" required>
                <option value="">{{ __('Select a category') }}</option>
                @foreach (\App\Modules\Assistant\Domain\Models\AssistantDocument::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $document->category) === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('The document address is created from the title when you save. Changing the title later does not change that address.') }}</p>
            <x-ui.input-error for="category" />
        </div>
        <div>
            <x-ui.label for="audience" required>{{ __('Who may hear this') }}</x-ui.label>
            <select id="audience" name="audience" class="form-control mt-1.5">
                <option value="public" @selected(old('audience', $document->audience ?: 'public') === 'public')>{{ __('Public, including guests') }}</option>
                <option value="investor" @selected(old('audience', $document->audience) === 'investor')>{{ __('Verified investors with a business profile') }}</option>
            </select>
            <x-ui.input-error for="audience" />
        </div>
        <div>
            <x-ui.label for="summary">{{ __('Summary') }}</x-ui.label>
            <x-ui.input id="summary" name="summary" :value="old('summary', $document->summary)" maxlength="280" />
            <x-ui.input-error for="summary" />
        </div>
        <div>
            <x-ui.label for="body" required>{{ __('Body') }}</x-ui.label>
            <textarea id="body" name="body" rows="12" required minlength="40" maxlength="20000" class="form-control mt-1.5">{{ old('body', $document->body) }}</textarea>
            <x-ui.input-error for="body" />
        </div>
        <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
            <input type="checkbox" name="is_published" value="1" class="form-check" @checked(old('is_published', $document->exists ? $document->is_published : true))>
            {{ __('Published for answers') }}
        </label>

        <x-ui.button>{{ $editing ? __('Save and index') : __('Create and index') }}</x-ui.button>
    </form>

    @if ($editing)
        <form method="POST" action="{{ route('admin.assistant.knowledge.destroy', $document) }}" class="mt-4">
            @csrf
            @method('DELETE')
            <x-ui.button variant="ghost">{{ __('Remove from answers') }}</x-ui.button>
        </form>
    @endif
</x-layouts.workspace>
