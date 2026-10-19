<x-layouts.workspace :title="__('Bulk SMS')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Prepare a message, choose who receives it, then send it through whichever gateway is active.') }}</p>
        <a href="{{ route('admin.sms.campaigns.create') }}" class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800">{{ __('New campaign') }}</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th class="px-5 py-3">{{ __('Campaign') }}</th>
                    <th class="px-5 py-3">{{ __('Audience') }}</th>
                    <th class="px-5 py-3">{{ __('Recipients') }}</th>
                    <th class="px-5 py-3">{{ __('Sent') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr class="border-t border-slate-200 dark:border-slate-800">
                        <td class="px-5 py-3"><a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.sms.campaigns.show', $campaign) }}">{{ $campaign->name }}</a></td>
                        <td class="px-5 py-3">{{ str_replace('_', ' ', $campaign->audience) }}</td>
                        <td class="px-5 py-3">{{ number_format($campaign->recipient_count) }}</td>
                        <td class="px-5 py-3">{{ number_format($campaign->sent_count) }}</td>
                        <td class="px-5 py-3">{{ $campaign->status }}</td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-6 text-slate-500 dark:text-slate-400" colspan="5">{{ __('No campaigns yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $campaigns->links() }}</div>
</x-layouts.workspace>
