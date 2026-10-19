<x-layouts.workspace :title="$enquiry->reference">
    <a href="{{ route('admin.contact') }}" class="text-sm font-semibold text-slate-600">{{ __('Back to enquiries') }}</a>
    <div class="mt-4 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ ucfirst($enquiry->status) }}</p>
            <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">{{ $enquiry->subject }}</h2>
            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $enquiry->message }}</p>
            <div class="mt-6 space-y-4">
                @foreach ($enquiry->replies as $reply)
                    <article class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="text-xs font-semibold text-slate-500">{{ $reply->author?->name }} · {{ $reply->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                        <p class="mt-2 whitespace-pre-line text-sm">{{ $reply->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
        <aside class="h-max rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-bold text-slate-900 dark:text-white">{{ $enquiry->name }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ $enquiry->email }}</p>
            <p class="text-sm text-slate-600">{{ $enquiry->phone_number ?: __('No phone') }}</p>
            @can('contact.manage')
                <form method="POST" action="{{ route('admin.contact.reply', $enquiry) }}" class="mt-4">
                    @csrf
                    <x-ui.label for="body" required>{{ __('Reply') }}</x-ui.label>
                    <textarea id="body" name="body" rows="5" required class="form-control">{{ old('body') }}</textarea>
                    <x-ui.input-error for="body" />
                    <x-ui.button class="mt-3" variant="gold">{{ __('Send reply') }}</x-ui.button>
                </form>
                @if ($enquiry->status !== 'closed')
                    <form method="POST" action="{{ route('admin.contact.close', $enquiry) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-slate-600">{{ __('Close enquiry') }}</button>
                    </form>
                @endif
            @endcan
        </aside>
    </div>
</x-layouts.workspace>
