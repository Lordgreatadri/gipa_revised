@php
    $kept = request()->only(['q', 'region', 'sector', 'sort', 'dir']);
    $nextDir = function (string $column): string {
        if (request('sort') !== $column) {
            return 'asc';
        }

        return request('dir', 'desc') === 'asc' ? 'desc' : 'asc';
    };
@endphp

<x-layouts.workspace :title="__('Opportunity Record')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Search and sort the records in your scope. Drafts that still need attention are marked in red.') }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.opportunities.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Overview') }}</a>
            @can('opportunities.create')
                <a href="{{ route('admin.opportunities.create') }}" class="cta bg-gold-500 text-brand-950 hover:bg-gold-400">
                    <i data-lucide="plus" class="size-4"></i>
                    {{ __('New opportunity') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2" role="tablist">
        <a href="{{ route('admin.opportunities.register', $kept + ['tab' => 'all']) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold', 'bg-brand-800 text-white' => $tab === 'all', 'bg-white text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700' => $tab !== 'all'])>{{ __('All opportunities') }}</a>
        <a href="{{ route('admin.opportunities.register', $kept + ['tab' => 'drafts']) }}" @class(['inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold', 'bg-brand-800 text-white' => $tab === 'drafts', 'bg-white text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700' => $tab !== 'drafts'])>
            {{ __('New drafts') }}
            @if (($counts['draft'] ?? 0) > 0)
                <span @class(['inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold', 'bg-red-600 text-white' => $tab !== 'drafts', 'bg-red-500 text-white' => $tab === 'drafts', 'animate-pulse' => $recentDrafts > 0])>{{ $counts['draft'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.opportunities.register', $kept + ['tab' => 'cancelled']) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold', 'bg-brand-800 text-white' => $tab === 'cancelled', 'bg-white text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700' => $tab !== 'cancelled'])>{{ __('Cancelled') }}</a>
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-2 lg:grid-cols-5 dark:border-slate-800 dark:bg-slate-900">
        <input type="hidden" name="tab" value="{{ $tab }}">
        @if (request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
        @endif
        <div>
            <x-ui.label for="q">{{ __('Title') }}</x-ui.label>
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
        <div>
            <x-ui.label for="sector">{{ __('Sector') }}</x-ui.label>
            <select id="sector" name="sector" class="form-control">
                <option value="">{{ __('All sectors') }}</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->uuid }}" @selected(request('sector') === $sector->uuid)>{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($tab === 'all')
            <div>
                <x-ui.label for="state">{{ __('Workflow') }}</x-ui.label>
                <select id="state" name="state" class="form-control">
                    <option value="">{{ __('All states') }}</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->value }}" @selected(request('state') === $state->value)>{{ $state->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex items-end gap-2">
            <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
            <a href="{{ route('admin.opportunities.register', ['tab' => $tab]) }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th><a class="link" href="{{ route('admin.opportunities.register', request()->except('page') + ['sort' => 'title', 'dir' => $nextDir('title'), 'tab' => $tab]) }}">{{ __('Opportunity') }}</a></th>
                    <th>{{ __('Place') }}</th>
                    <th><a class="link" href="{{ route('admin.opportunities.register', request()->except('page') + ['sort' => 'amount', 'dir' => $nextDir('amount'), 'tab' => $tab]) }}">{{ __('Investment') }}</a></th>
                    <th><a class="link" href="{{ route('admin.opportunities.register', request()->except('page') + ['sort' => 'updated', 'dir' => $nextDir('updated'), 'tab' => $tab]) }}">{{ __('Workflow') }}</a></th>
                    <th><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opportunities as $opportunity)
                    <tr>
                        <td>
                            <span class="block font-semibold text-slate-900 dark:text-white">{{ $opportunity->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $opportunity->reference }} · {{ $opportunity->sector?->name }}</span>
                        </td>
                        <td>{{ $opportunity->district?->name }}<span class="mt-1 block text-xs text-slate-500">{{ $opportunity->district?->region?->name }}</span></td>
                        <td>{{ $opportunity->currency }} {{ number_format((float) $opportunity->investment_amount, 2) }}</td>
                        <td><span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 dark:bg-brand-950 dark:text-brand-200">{{ $opportunity->workflow_state->label() }}</span></td>
                        <td class="text-right"><a href="{{ route('admin.opportunities.show', $opportunity) }}" class="link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $opportunities->links() }}</div>
</x-layouts.workspace>
