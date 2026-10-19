<x-layouts.workspace :title="__('Audit log')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Material actions recorded for investigation. Records cannot be edited or deleted. An export is itself recorded.') }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.audit.export', request()->query() + ['format' => 'csv']) }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('CSV') }}</a>
            <a href="{{ route('admin.audit.export', request()->query() + ['format' => 'xls']) }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Excel') }}</a>
        </div>
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-2 lg:grid-cols-5 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <x-ui.label for="q">{{ __('Search') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="80" />
        </div>
        <div>
            <x-ui.label for="action">{{ __('Action') }}</x-ui.label>
            <select id="action" name="action" class="form-control">
                <option value="">{{ __('All actions') }}</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-ui.label for="from">{{ __('From') }}</x-ui.label>
            <x-ui.input id="from" name="from" type="date" :value="request('from')" />
        </div>
        <div>
            <x-ui.label for="to">{{ __('To') }}</x-ui.label>
            <x-ui.input id="to" name="to" type="date" :value="request('to')" />
        </div>
        <div class="flex items-end gap-3">
            <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
            <a href="{{ route('admin.audit') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Actor') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Request') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                        <td>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $record->event ?: $record->description }}</span>
                            @if ($record->properties?->isNotEmpty())
                                <details class="mt-1">
                                    <summary class="cursor-pointer text-xs font-semibold text-brand-700 dark:text-brand-300">{{ __('Context') }}</summary>
                                    <pre class="mt-2 max-w-xl overflow-x-auto whitespace-pre-wrap text-xs text-slate-600 dark:text-slate-300">{{ json_encode($record->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td>{{ $record->causer?->name ?? __('System') }}</td>
                        <td>{{ $record->source }}<span class="mt-1 block text-xs text-slate-500">{{ $record->ip_address }}</span></td>
                        <td class="text-xs text-slate-500">{{ $record->request_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $records->links() }}</div>
</x-layouts.workspace>
