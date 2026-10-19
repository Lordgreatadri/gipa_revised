@php
    $counts = $overview['counts'];
    $cards = [
        ['state' => 'draft', 'label' => __('Draft'), 'icon' => 'file-pen', 'wrap' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200', 'iconWrap' => 'bg-amber-500 text-white'],
        ['state' => 'pending_approval', 'label' => __('Pending approval'), 'icon' => 'hourglass', 'wrap' => 'bg-sky-50 text-sky-800 dark:bg-sky-500/15 dark:text-sky-200', 'iconWrap' => 'bg-sky-600 text-white'],
        ['state' => 'approved', 'label' => __('Approved'), 'icon' => 'badge-check', 'wrap' => 'bg-indigo-50 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200', 'iconWrap' => 'bg-indigo-600 text-white'],
        ['state' => 'active', 'label' => __('Active'), 'icon' => 'circle-play', 'wrap' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200', 'iconWrap' => 'bg-emerald-600 text-white'],
        ['state' => 'completed', 'label' => __('Completed'), 'icon' => 'flag', 'wrap' => 'bg-teal-50 text-teal-800 dark:bg-teal-500/15 dark:text-teal-200', 'iconWrap' => 'bg-teal-600 text-white'],
        ['state' => 'cancelled', 'label' => __('Cancelled'), 'icon' => 'ban', 'wrap' => 'bg-rose-50 text-rose-800 dark:bg-rose-500/15 dark:text-rose-200', 'iconWrap' => 'bg-rose-600 text-white'],
    ];
@endphp

<x-layouts.workspace :title="__('Opportunity Overview')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('District entry, review, and investor visibility, counted from the records you are allowed to see.') }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.opportunities.register') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">
                <i data-lucide="list" class="size-4"></i>
                {{ __('View Record') }}
            </a>
            @can('opportunities.create')
                <a href="{{ route('admin.opportunities.create') }}" class="cta bg-gold-500 text-brand-950 hover:bg-gold-400">
                    <i data-lucide="plus" class="size-4"></i>
                    {{ __('New opportunity') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.opportunities.register') }}" class="rounded-2xl bg-brand-600 p-5 text-white shadow-card">
            <span class="flex size-10 items-center justify-center rounded-xl bg-white/15"><i data-lucide="map-pinned" class="size-5"></i></span>
            <p class="mt-4 text-xs font-bold uppercase tracking-wider text-brand-100">{{ __('All opportunities') }}</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($overview['total']) }}</p>
        </a>
        <a href="{{ route('admin.opportunities.register', ['tab' => 'drafts']) }}" class="relative rounded-2xl bg-amber-300 p-5 text-amber-950 shadow-card">
            @if ($overview['recentDrafts'] > 0)
                <span class="absolute right-4 top-4 inline-flex size-3 animate-pulse rounded-full bg-red-600" title="{{ __('New drafts') }}"></span>
            @endif
            <span class="flex size-10 items-center justify-center rounded-xl bg-white/30"><i data-lucide="file-pen" class="size-5"></i></span>
            <p class="mt-4 text-xs font-bold uppercase tracking-wider">{{ __('Drafts awaiting attention') }}</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($overview['draftCount']) }}</p>
        </a>
        <div class="rounded-2xl bg-emerald-500 p-5 text-white shadow-card">
            <span class="flex size-10 items-center justify-center rounded-xl bg-white/15"><i data-lucide="banknote" class="size-5"></i></span>
            <p class="mt-4 text-xs font-bold uppercase tracking-wider text-emerald-100">{{ __('Investment in GHS') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($overview['currencies']['GHS'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-sky-600 p-5 text-white shadow-card">
            <span class="flex size-10 items-center justify-center rounded-xl bg-white/15"><i data-lucide="dollar-sign" class="size-5"></i></span>
            <p class="mt-4 text-xs font-bold uppercase tracking-wider text-sky-100">{{ __('Investment in USD') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($overview['currencies']['USD'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ($cards as $card)
            <a href="{{ route('admin.opportunities.register', ['state' => $card['state'], 'tab' => 'all']) }}" class="rounded-2xl p-4 shadow-card {{ $card['wrap'] }}">
                <span class="flex size-9 items-center justify-center rounded-lg {{ $card['iconWrap'] }}"><i data-lucide="{{ $card['icon'] }}" class="size-4"></i></span>
                <p class="mt-3 text-[0.7rem] font-bold uppercase tracking-wider">{{ $card['label'] }}</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($counts[$card['state']] ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    <div id="opportunity-chart-data" hidden>@json($overview['charts'])</div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Opportunities by region') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Draft, approved, active and cancelled records entered in :year, for every region.', ['year' => $overview['charts']['regional']['year']]) }}</p>
            </div>
            <div class="quarter-switch grid grid-cols-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700" role="group" aria-label="{{ __('Quarter') }}">
                @foreach ([1, 2, 3, 4] as $quarter)
                    <button type="button" data-quarter="{{ $quarter }}" aria-pressed="{{ $overview['charts']['regional']['current'] === $quarter ? 'true' : 'false' }}">{{ __('QTR :quarter', ['quarter' => $quarter]) }}</button>
                @endforeach
            </div>
        </div>
        <div id="chart-regional" class="mt-4 h-96"></div>
    </section>

    <div class="mt-6 grid gap-4 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Opportunities by sector') }}</h2>
            <div id="chart-sectors" class="mt-3 h-80"></div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Top regions') }}</h2>
            <div id="chart-regions" class="mt-3 h-80"></div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Workflow mix') }}</h2>
            <div id="chart-states" class="mt-3 h-80"></div>
            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm font-medium text-slate-800 dark:text-slate-50">
                @foreach ($overview['charts']['states']['labels'] as $index => $label)
                    <li class="inline-flex items-center gap-2">
                        <span @class([
                            'size-2.5 rounded-full',
                            'bg-[#0f6e56]' => $index === 0,
                            'bg-[#d4a017]' => $index === 1,
                            'bg-[#0369a1]' => $index === 2,
                            'bg-[#4f46e5]' => $index === 3,
                            'bg-[#0d9488]' => $index === 4,
                            'bg-[#e11d48]' => $index === 5,
                        ])></span>
                        {{ $label }}
                        <span class="font-semibold">{{ number_format($overview['charts']['states']['values'][$index] ?? 0) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Entries over the last 14 days') }}</h2>
            <div id="chart-trend" class="mt-3 h-80"></div>
        </section>
    </div>
</x-layouts.workspace>
