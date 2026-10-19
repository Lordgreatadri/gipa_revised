<x-layouts.workspace :title="$engagement->reference">
    <a href="{{ route('investor.engagement') }}" class="text-sm font-semibold text-slate-600">{{ __('Back to engagement') }}</a>
    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $engagement->kind->label() }} · {{ $engagement->state->label() }}</p>
        <h2 class="mt-2 text-xl font-bold">
            @if ($engagement->opportunity)
                <a class="link" href="{{ route('investor.opportunities.show', $engagement->opportunity) }}">{{ $engagement->opportunity->title }}</a>
            @endif
        </h2>
        <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $engagement->message }}</p>
        <div class="mt-6 space-y-3">
            @foreach ($engagement->messages as $message)
                <article class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500">{{ $message->author?->name }} · {{ $message->created_at?->diffForHumans() }}</p>
                    <p class="mt-2 text-sm">{{ $message->body }}</p>
                </article>
            @endforeach
        </div>
        <form method="POST" action="{{ route('investor.engagement.messages.store', $engagement) }}" class="mt-6">
            @csrf
            <x-ui.label for="body" required>{{ __('Message to GIPA') }}</x-ui.label>
            <textarea id="body" name="body" rows="4" required class="form-control">{{ old('body') }}</textarea>
            <x-ui.input-error for="body" />
            <x-ui.button class="mt-3" variant="gold">{{ __('Send message') }}</x-ui.button>
        </form>
    </section>
</x-layouts.workspace>
