<x-layouts.workspace :title="$title">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $intro }}</p>
        <a href="{{ $listRoute }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">
            <i data-lucide="list" class="size-4"></i>
            {{ $listLabel }}
        </a>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-brand-800 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-brand-100">{{ __('Records') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['total']) }}</p></div>
        <div class="rounded-2xl bg-emerald-600 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-emerald-100">{{ __('Active') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['active']) }}</p></div>
        <div class="rounded-2xl bg-sky-700 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-sky-100">{{ __('Opportunities') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['opportunities']) }}</p></div>
    </div>

    <div id="reference-chart-data" hidden>@json($chart)</div>
    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Opportunities using these records') }}</h2>
        <div id="chart-reference" class="mt-3 h-80"></div>
    </section>
</x-layouts.workspace>
