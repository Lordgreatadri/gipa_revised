<x-layouts.workspace :title="__('Reporting')">
    <p class="max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Formal report templates will be published here. The figures that can already be read from the register are on the dashboard: regional distribution, sector counts, the opportunity timeline, and the lifecycle snapshot.') }}</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['chart-column', __('Regional distribution'), __('Opportunities counted by region. The dashboard chart is the current view.')],
            ['factory', __('Sector investment'), __('Opportunities counted by sector. GHS and USD totals stay separate on the dashboard.')],
            ['route', __('Opportunity timeline'), __('Records opened over the last 14 days.')],
            ['layers', __('Lifecycle snapshot'), __('Draft, pending approval, approved, active, completed, and cancelled.')],
            ['handshake', __('Engagement'), __('Inquiries and expressions of interest. Review states beyond submitted are not defined yet.')],
            ['scroll-text', __('Audit extract'), __('The audit log can be exported by roles that are allowed to review it.')],
        ] as [$icon, $title, $body])
            <article class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-center gap-2 text-brand-800 dark:text-brand-200"><i data-lucide="{{ $icon }}" class="size-4"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h2></div>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $body }}</p>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Placeholder') }}</p>
            </article>
        @endforeach
    </div>

    <p class="mt-6 text-sm text-slate-600 dark:text-slate-300">
        <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.dashboard') }}">{{ __('Open the dashboard') }}</a>
    </p>
</x-layouts.workspace>
