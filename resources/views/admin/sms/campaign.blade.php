<x-layouts.workspace :title="$campaign->name">
    <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">
        <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.sms.campaigns') }}">{{ __('Bulk SMS') }}</a>
        <span aria-hidden="true"> / </span>{{ $campaign->name }}
    </p>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $campaign->status }} · {{ str_replace('_', ' ', $campaign->audience) }}</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ number_format($campaign->recipient_count) }} {{ __('recipients') }} · {{ number_format($campaign->sent_count) }} {{ __('sent') }} · {{ number_format($campaign->failed_count) }} {{ __('not sent') }}</p>
            </div>
            @if ($campaign->status === 'draft')
                <form method="POST" action="{{ route('admin.sms.campaigns.send', $campaign) }}">
                    @csrf
                    <button class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Send campaign') }}</button>
                </form>
            @endif
        </div>
        <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-800 dark:bg-slate-950 dark:text-slate-100">{{ $campaign->body }}</div>
    </section>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th class="px-5 py-3">{{ __('Name') }}</th>
                    <th class="px-5 py-3">{{ __('Business') }}</th>
                    <th class="px-5 py-3">{{ __('Phone') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recipients as $recipient)
                    <tr class="border-t border-slate-200 dark:border-slate-800">
                        <td class="px-5 py-3">{{ $recipient->name ?: '—' }}</td>
                        <td class="px-5 py-3">{{ $recipient->business_name ?: '—' }}</td>
                        <td class="px-5 py-3">{{ $recipient->phone }}</td>
                        <td class="px-5 py-3">{{ $recipient->status }}@if ($recipient->detail) — {{ $recipient->detail }}@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $recipients->links() }}</div>
</x-layouts.workspace>
