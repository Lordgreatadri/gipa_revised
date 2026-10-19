@php
    $sectorUuids = $sectors->pluck('uuid', 'id');
    $filterData = [
        'districts' => $districts->map(fn ($district) => [
            'uuid' => $district->uuid,
            'name' => $district->name,
            'region' => $district->region?->uuid,
        ])->values(),
        'subSectors' => $subSectors->map(fn ($subSector) => [
            'uuid' => $subSector->uuid,
            'name' => $subSector->name,
            'sector' => $sectorUuids[$subSector->sector_id] ?? null,
        ])->values(),
        'selectedDistrict' => request('district'),
        'selectedSub' => request('sub_sector'),
    ];
@endphp

<x-layouts.workspace :title="__('Opportunities')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Verified pipeline') }}</p>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Approved and active opportunities across Ghana. Drafts and records still under review are not listed.') }}</p>
        </div>
        <a href="{{ route('investor.map') }}#investment-map" class="cta bg-brand-800 text-white hover:bg-brand-700">
            <i data-lucide="map" class="size-4"></i>
            {{ __('Open investment map') }}
        </a>
    </div>

    <form method="GET" class="mb-6 grid gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-card lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900" x-data="investorFilters">
        <div id="investor-filter-data" hidden>@json($filterData)</div>
        <div class="lg:col-span-2">
            <x-ui.label for="q">{{ __('Keyword') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="80" placeholder="{{ __('Title, district or sector') }}" />
        </div>
        <div>
            <x-ui.label for="region">{{ __('Region') }}</x-ui.label>
            <select id="region" name="region" class="form-control" data-region-select x-on:change="filterPlaces">
                <option value="">{{ __('All regions') }}</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->uuid }}" @selected(request('region') === $region->uuid)>{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-ui.label for="district">{{ __('District') }}</x-ui.label>
            <select id="district" name="district" class="form-control" data-district-select>
                <option value="">{{ __('All districts') }}</option>
            </select>
        </div>
        <div>
            <x-ui.label for="sector">{{ __('Sector') }}</x-ui.label>
            <select id="sector" name="sector" class="form-control" data-sector-select x-on:change="filterSubSectors">
                <option value="">{{ __('All sectors') }}</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->uuid }}" @selected(request('sector') === $sector->uuid)>{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-ui.label for="sub_sector">{{ __('Sub-sector') }}</x-ui.label>
            <select id="sub_sector" name="sub_sector" class="form-control" data-subsector-select>
                <option value="">{{ __('All sub-sectors') }}</option>
            </select>
        </div>
        <div>
            <x-ui.label for="type">{{ __('Enterprise type') }}</x-ui.label>
            <select id="type" name="type" class="form-control">
                <option value="">{{ __('All types') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type->uuid }}" @selected(request('type') === $type->uuid)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-ui.label for="sort">{{ __('Sort by') }}</x-ui.label>
            <select id="sort" name="sort" class="form-control">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>{{ __('Recently published') }}</option>
                <option value="amount_desc" @selected(request('sort') === 'amount_desc')>{{ __('Investment: high to low') }}</option>
                <option value="amount_asc" @selected(request('sort') === 'amount_asc')>{{ __('Investment: low to high') }}</option>
            </select>
        </div>
        <div>
            <x-ui.label for="min_amount">{{ __('Minimum amount') }}</x-ui.label>
            <x-ui.input id="min_amount" name="min_amount" type="number" class="amount-stepper" min="0" step="1000" :value="request('min_amount')" inputmode="numeric" />
            <p class="mt-1 text-xs text-slate-500">{{ __('Type a number or use the arrows.') }}</p>
        </div>
        <div>
            <x-ui.label for="max_amount">{{ __('Maximum amount') }}</x-ui.label>
            <x-ui.input id="max_amount" name="max_amount" type="number" class="amount-stepper" min="0" step="1000" :value="request('max_amount')" inputmode="numeric" />
            <p class="mt-1 text-xs text-slate-500">{{ __('Type a number or use the arrows.') }}</p>
        </div>
        <div class="flex items-end gap-3 lg:col-span-2">
            <x-ui.button>{{ __('Apply filters') }}</x-ui.button>
            <a href="{{ route('investor.opportunities') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Clear all') }}</a>
        </div>
    </form>

    <p class="mb-4 text-sm text-slate-600 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">{{ number_format($opportunities->total()) }}</strong> {{ trans_choice('verified result|verified results', $opportunities->total()) }}</p>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($opportunities as $opportunity)
            <article class="flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
                <a href="{{ route('investor.opportunities.show', $opportunity) }}" class="opportunity-tile relative flex h-32 items-end p-4">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-white/80 text-sm font-bold text-brand-800 shadow-sm">{{ strtoupper(substr((string) $opportunity->sector?->name, 0, 2)) }}</span>
                </a>
                <div class="flex flex-1 flex-col p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ $opportunity->sector?->name }}@if ($opportunity->subSector) · {{ $opportunity->subSector->name }}@endif</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900 dark:text-white"><a href="{{ route('investor.opportunities.show', $opportunity) }}" class="hover:underline">{{ $opportunity->title }}</a></h2>
                    <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($opportunity->description, 80) }}</p>
                    <p class="mt-3 inline-flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300"><i data-lucide="map-pin" class="size-4 text-brand-700"></i>{{ $opportunity->district?->name }}, {{ $opportunity->district?->region?->name }}</p>
                    <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <div>
                            <p class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-500">{{ $opportunity->enterpriseType?->name }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $opportunity->currency }} {{ number_format((float) $opportunity->investment_amount, 0) }}</p>
                        </div>
                        <a href="{{ route('investor.opportunities.show', $opportunity) }}" class="link">{{ __('View record') }}</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-500 md:col-span-2 xl:col-span-3">{{ __('No records are available yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $opportunities->links() }}</div>
</x-layouts.workspace>
