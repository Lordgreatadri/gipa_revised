<x-layouts.workspace :title="__('SMS messages')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Every outbound message, including tests and campaigns. Open a row to read the text and the delivery reports.') }}</p>
        @can(\App\Modules\Identity\Domain\Access\AccessPermissions::SMS_CAMPAIGNS_MANAGE)
            <a href="{{ route('admin.sms.campaigns.create') }}" class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800">{{ __('New bulk SMS') }}</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <x-ui.label for="q">{{ __('Reference or phone') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="40" />
        </div>
        <div>
            <x-ui.label for="status">{{ __('Status') }}</x-ui.label>
            <select id="status" name="status" class="form-control">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['queued', 'sent', 'delivered', 'undelivered', 'failed', 'skipped'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-3">
            <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
            <a href="{{ route('admin.sms.messages') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th class="px-5 py-3">{{ __('Reference') }}</th>
                    <th class="px-5 py-3">{{ __('Recipient') }}</th>
                    <th class="px-5 py-3">{{ __('Gateway') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('When') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr class="border-t border-slate-200 dark:border-slate-800">
                        <td class="px-5 py-3"><a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.sms.messages.show', $message) }}">{{ $message->reference }}</a></td>
                        <td class="px-5 py-3">{{ $message->recipient }}</td>
                        <td class="px-5 py-3">{{ $message->provider }}</td>
                        <td class="px-5 py-3">{{ $message->status }}</td>
                        <td class="px-5 py-3">{{ $message->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-6 text-slate-500 dark:text-slate-400" colspan="5">{{ __('No messages match this filter.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
</x-layouts.workspace>
