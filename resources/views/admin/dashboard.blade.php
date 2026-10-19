<x-layouts.workspace :title="__('Dashboard')">
    <div class="mb-6 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ __('Portfolio') }}</p>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                @if ($portfolio['restricted'])
                    {{ __('These figures are the opportunities in your assigned districts that you created, were assigned, or moved.') }}
                @else
                    {{ __('National view of the investment register, the queues your role can open, and the latest record movement.') }}
                @endif
            </p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-slate-500"><i data-lucide="shield" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Staff accounts') }}</p></div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['accounts']['staff']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-slate-500"><i data-lucide="users" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Investor accounts') }}</p></div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['accounts']['investors']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-slate-500"><i data-lucide="key-round" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Roles') }}</p></div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['accounts']['roles']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-slate-500"><i data-lucide="lock-keyhole" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Permissions') }}</p></div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['accounts']['permissions']) }}</p>
        </article>
    </div>

    @if ($portfolio['opportunities'])
        @php $overview = $portfolio['opportunities']; @endphp
        <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl bg-brand-800 p-5 text-white shadow-card">
                <div class="flex items-center gap-2 text-brand-100"><i data-lucide="map-pinned" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Opportunities') }}</p></div>
                <p class="font-display mt-3 text-3xl font-bold">{{ number_format($overview['total']) }}</p>
            </article>
            <article class="rounded-2xl bg-emerald-700 p-5 text-white shadow-card">
                <div class="flex items-center gap-2 text-emerald-100"><i data-lucide="circle-play" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Active') }}</p></div>
                <p class="font-display mt-3 text-3xl font-bold">{{ number_format($overview['counts']['active'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-emerald-100">{{ __('Visible to verified investors') }}</p>
            </article>
            <article class="rounded-2xl bg-amber-600 p-5 text-white shadow-card">
                <div class="flex items-center gap-2 text-amber-50"><i data-lucide="banknote" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('GHS on record') }}</p></div>
                <p class="font-display mt-3 text-2xl font-bold">{{ number_format((float) ($overview['currencies']['GHS'] ?? 0), 2) }}</p>
            </article>
            <article class="rounded-2xl bg-sky-800 p-5 text-white shadow-card">
                <div class="flex items-center gap-2 text-sky-100"><i data-lucide="dollar-sign" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('USD on record') }}</p></div>
                <p class="font-display mt-3 text-2xl font-bold">{{ number_format((float) ($overview['currencies']['USD'] ?? 0), 2) }}</p>
            </article>
        </div>

        <div id="dashboard-chart-data" hidden>@json($overview['charts'])</div>

        <div class="mt-6 grid gap-4 xl:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2"><i data-lucide="chart-column" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Workflow mix') }}</h2></div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Where each opportunity sits in approval.') }}</p>
                <div id="chart-dashboard-states" class="mt-2 h-80"></div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2"><i data-lucide="route" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Opportunity timeline') }}</h2></div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Records opened over the last 14 days.') }}</p>
                <div id="chart-dashboard-trend" class="mt-2 h-80"></div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2"><i data-lucide="map" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Regional distribution') }}</h2></div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opportunities by region.') }}</p>
                <div id="chart-dashboard-regions" class="mt-2 h-80"></div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2"><i data-lucide="factory" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Sector investment') }}</h2></div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opportunities by sector. GHS and USD stay in the tiles above.') }}</p>
                <div id="chart-dashboard-sectors" class="mt-2 h-80"></div>
            </section>
        </div>
    @endif

    @if ($portfolio['profiles'] !== null || $portfolio['engagements'] !== null || $portfolio['contacts'] !== null)
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            @if ($portfolio['profiles'] !== null)
                <a href="{{ route('admin.profiles.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2 text-slate-500"><i data-lucide="badge-check" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Profiles awaiting approval') }}</p></div>
                    <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['profiles']) }}</p>
                </a>
            @endif
            @if ($portfolio['engagements'] !== null)
                <a href="{{ route('admin.engagement') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2 text-slate-500"><i data-lucide="handshake" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('Inquiries and interest') }}</p></div>
                    <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['engagements']) }}</p>
                </a>
            @endif
            @if ($portfolio['contacts'] !== null)
                <a href="{{ route('admin.contact') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2 text-slate-500"><i data-lucide="mail" class="size-4"></i><p class="text-xs font-bold uppercase tracking-wider">{{ __('New contact enquiries') }}</p></div>
                    <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($portfolio['contacts']) }}</p>
                </a>
            @endif
        </div>
    @endif

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2"><i data-lucide="zap" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Recent activity') }}</h2></div>
            <ol class="mt-4 grid gap-3">
                @forelse ($portfolio['activity'] as $event)
                    <li class="border-t border-slate-100 pt-3 text-sm first:border-t-0 first:pt-0 dark:border-slate-800">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $event->opportunity?->title ?: __('Opportunity') }}</p>
                        <p class="text-slate-600 dark:text-slate-300">{{ $event->actor?->name ?: __('System') }} · {{ $event->to_state?->label() }}</p>
                        <p class="text-xs text-slate-500">{{ $event->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                    </li>
                @empty
                    <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('No opportunity movement is in view yet.') }}</li>
                @endforelse
            </ol>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2"><i data-lucide="scroll-text" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Audit log') }}</h2></div>
                @if ($portfolio['audit'] !== null)
                    <a class="text-xs font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.audit') }}">{{ __('Open') }}</a>
                @endif
            </div>
            @if ($portfolio['audit'] === null)
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ __('The audit log is available to roles that can review it.') }}</p>
            @else
                <ol class="mt-4 grid gap-3">
                    @forelse ($portfolio['audit'] as $record)
                        <li class="border-t border-slate-100 pt-3 text-sm first:border-t-0 first:pt-0 dark:border-slate-800">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $record->event ?: $record->description }}</p>
                            <p class="text-xs text-slate-500">{{ $record->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('No audit entries yet.') }}</li>
                    @endforelse
                </ol>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2"><i data-lucide="layers" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Lifecycle snapshot') }}</h2></div>
            @if ($portfolio['lifecycle'] === [])
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ __('Opportunity figures appear for roles that can view the register.') }}</p>
            @else
                <ul class="mt-4 grid grid-cols-2 gap-3">
                    @foreach ($portfolio['lifecycle'] as $state)
                        <li class="rounded-xl bg-slate-50 px-3 py-3 dark:bg-slate-950">
                            <div class="flex items-center gap-2 text-slate-500"><i data-lucide="{{ $state['icon'] }}" class="size-4"></i><p class="text-xs font-semibold">{{ $state['label'] }}</p></div>
                            <p class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($state['value']) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    @if ($portfolio['logins'] !== null)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2"><i data-lucide="log-in" class="size-4 text-brand-700 dark:text-brand-300"></i><h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Login history') }}</h2></div>
                <a class="text-xs font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.logins.index') }}">{{ __('Open') }}</a>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="data-table min-w-full text-left">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Result') }}</th>
                            <th>{{ __('Location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($portfolio['logins'] as $attempt)
                            <tr>
                                <td>{{ $attempt->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $attempt->email }}</td>
                                <td>{{ $attempt->outcomeLabel() }}</td>
                                <td>{{ $attempt->placeLabel() !== '' ? $attempt->placeLabel() : ($attempt->ip_address ?: __('Not available')) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-slate-500">{{ __('No sign-in attempts are recorded yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-layouts.workspace>
