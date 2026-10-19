<x-layouts.workspace :title="__('District overview')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('How districts are spread across the regions in your scope.') }}</p>
        <a href="{{ route('admin.districts.register') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">
            <i data-lucide="list" class="size-4"></i>
            {{ __('View records') }}
        </a>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-brand-800 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-brand-100">{{ __('Regions') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['regions']) }}</p></div>
        <div class="rounded-2xl bg-sky-700 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-sky-100">{{ __('Districts') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['districts']) }}</p></div>
        <div class="rounded-2xl bg-emerald-600 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-emerald-100">{{ __('With opportunities') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['withOpportunities']) }}</p></div>
    </div>

    <div id="reference-chart-data" hidden>@json($chart)</div>
    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Districts by region') }}</h2>
        <div id="chart-reference" class="mt-3 h-80"></div>
    </section>
</x-layouts.workspace>
