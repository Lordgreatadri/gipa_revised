@php
    $nextDir = function (string $column): string {
        if (request('sort') !== $column) {
            return 'asc';
        }

        return request('dir', 'asc') === 'asc' ? 'desc' : 'asc';
    };
@endphp

<x-layouts.workspace :title="__('District records')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Search the district register. Counts include only opportunities you are allowed to see.') }}</p>
        <a href="{{ route('admin.districts.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Overview') }}</a>
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <x-ui.label for="q">{{ __('Search') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="80" />
        </div>
        <div>
            <x-ui.label for="region">{{ __('Region') }}</x-ui.label>
            <select id="region" name="region" class="form-control">
                <option value="">{{ __('All regions') }}</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->uuid }}" @selected(request('region') === $region->uuid)>{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-3">
            <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
            <a href="{{ route('admin.districts.register') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th><a class="link" href="{{ route('admin.districts.register', request()->except('page') + ['sort' => 'name', 'dir' => $nextDir('name')]) }}">{{ __('District') }}</a></th>
                    <th><a class="link" href="{{ route('admin.districts.register', request()->except('page') + ['sort' => 'code', 'dir' => $nextDir('code')]) }}">{{ __('Code') }}</a></th>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Opportunities') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($districts as $district)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">{{ $district->name }}</td>
                        <td>{{ $district->code }}</td>
                        <td>{{ $district->region?->name }}</td>
                        <td>{{ number_format($district->opportunities_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $districts->links() }}</div>
</x-layouts.workspace>
