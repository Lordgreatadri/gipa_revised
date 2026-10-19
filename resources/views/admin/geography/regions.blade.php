<x-layouts.workspace :title="__('Regions')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Ghana’s regions, with the districts and opportunities you are allowed to see.') }}</p>
        <a href="{{ route('admin.districts.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('District overview') }}</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-brand-800 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-brand-100">{{ __('Regions') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['regions']) }}</p></div>
        <div class="rounded-2xl bg-sky-700 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-sky-100">{{ __('Districts') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['districts']) }}</p></div>
        <div class="rounded-2xl bg-emerald-600 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-emerald-100">{{ __('Opportunities') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['opportunities']) }}</p></div>
    </div>

    <form method="GET" class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="sm:w-80">
            <x-ui.label for="q">{{ __('Search') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="80" />
        </div>
        <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Capital') }}</th>
                    <th>{{ __('Districts') }}</th>
                    <th>{{ __('Opportunities') }}</th>
                    <th><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($regions as $region)
                    <tr>
                        <td><span class="block font-semibold text-slate-900 dark:text-white">{{ $region->name }}</span><span class="mt-1 block text-xs text-slate-500">{{ $region->code }}</span></td>
                        <td>{{ $region->capital }}</td>
                        <td>{{ number_format($region->districts_count) }}</td>
                        <td>{{ number_format($region->opportunities_count) }}</td>
                        <td class="text-right"><a class="link" href="{{ route('admin.districts.register', ['region' => $region->uuid]) }}">{{ __('Districts') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $regions->links() }}</div>
</x-layouts.workspace>
