<x-layouts.workspace :title="$message->reference">
    <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">
        <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.sms.messages') }}">{{ __('SMS messages') }}</a>
        <span aria-hidden="true"> / </span>{{ $message->reference }}
    </p>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $message->status }}</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $message->reference }}</h2>
                </div>
                @if ($message->canBeResent())
                    <form method="POST" action="{{ route('admin.sms.messages.resend', $message) }}">
                        @csrf
                        <button class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Send again') }}</button>
                    </form>
                @endif
            </div>
            <dl class="mt-4 grid gap-2 text-sm text-slate-700 dark:text-slate-200">
                <div class="flex justify-between gap-3"><dt>{{ __('Recipient') }}</dt><dd class="font-semibold">{{ $message->recipient }}</dd></div>
                <div class="flex justify-between gap-3"><dt>{{ __('Gateway') }}</dt><dd>{{ $message->provider }}</dd></div>
                <div class="flex justify-between gap-3"><dt>{{ __('Context') }}</dt><dd>{{ $message->context }}</dd></div>
                <div class="flex justify-between gap-3"><dt>{{ __('Queued') }}</dt><dd>{{ $message->queued_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt>{{ __('Sent') }}</dt><dd>{{ $message->sent_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt>{{ __('Delivered') }}</dt><dd>{{ $message->delivered_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd></div>
            </dl>
            @if ($message->canBeResent() && in_array($message->status, ['queued', 'sent'], true))
                <p class="mt-4 text-sm text-amber-800 dark:text-amber-200">{{ __('This message has been waiting for more than an hour without a delivery report. Send again only if you still want it to go out.') }}</p>
            @endif
            @if ($message->error)
                <p class="mt-4 rounded-xl bg-rose-50 px-3 py-2 text-sm text-rose-900 dark:bg-rose-950 dark:text-rose-100">{{ $message->error }}</p>
            @endif
            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-800 dark:bg-slate-950 dark:text-slate-100">{{ $message->body }}</div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Delivery reports') }}</h2>
            <ol class="mt-4 grid gap-3">
                @forelse ($events as $event)
                    <li class="rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $event->status }} <span class="font-normal text-slate-500">{{ $event->raw_status }}</span></p>
                        @if ($event->detail)<p class="text-slate-600 dark:text-slate-300">{{ $event->detail }}</p>@endif
                        <p class="text-xs text-slate-500">{{ $event->occurred_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                    </li>
                @empty
                    <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('No delivery report has arrived for this message.') }}</li>
                @endforelse
            </ol>
        </section>
    </div>
</x-layouts.workspace>
