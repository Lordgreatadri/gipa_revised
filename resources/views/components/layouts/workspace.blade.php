@props(['title' => null])

@php
    $account = auth()->user();
    $groups = $account->isStaff()
        ? app(\App\Modules\Identity\Presentation\Navigation\AdminMenu::class)->groups($account)
        : app(\App\Modules\InvestorOnboarding\Presentation\Navigation\InvestorMenu::class)->groups($account);
@endphp

<x-layouts.base :title="$title" class="bg-slate-100 dark:bg-slate-950">
    <a href="#workspace-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-gold-400 focus:px-3 focus:py-2 focus:text-sm focus:font-bold focus:text-brand-950">{{ __('Skip to content') }}</a>

    <div class="min-h-full lg:grid lg:h-dvh lg:grid-cols-[17.5rem_minmax(0,1fr)] lg:overflow-hidden" x-data="workspaceShell">
        <div class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden" x-bind:hidden="isClosed" x-on:click="close"></div>

        <aside
            id="workspace-navigation"
            class="workspace-sidebar fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] flex-col overflow-hidden border-r border-slate-200 bg-white lg:static lg:h-dvh lg:max-h-dvh lg:w-auto lg:max-w-none dark:border-slate-800 dark:bg-slate-950"
            x-bind:class="drawerClass"
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 dark:border-slate-800">
                <x-ui.brand variant="mark" img-class="h-9 w-auto max-h-9 max-w-[7rem] object-contain" />
                <button type="button" class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700 lg:hidden dark:border-slate-700 dark:text-slate-200" x-on:click="close" aria-label="{{ __('Close navigation') }}">
                    <i data-lucide="x" class="size-4"></i>
                </button>
            </div>

            <nav class="min-h-0 flex-1 space-y-2 overflow-y-auto px-3 py-4" aria-label="{{ __('Workspace') }}">
                @foreach ($groups as $group)
                    @php
                        $groupOpen = collect($group['items'])->contains(fn (array $item): bool => request()->routeIs(...(array) $item['active']));
                    @endphp
                    <details class="group" @if ($groupOpen) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-2 py-2 text-[0.7rem] font-bold uppercase tracking-[0.16em] text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/5">
                            <span>{{ $group['label'] }}</span>
                            <i data-lucide="chevron-down" class="nav-chevron size-4 transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-1 grid gap-1">
                            @foreach ($group['items'] as $item)
                                @php
                                    $itemActive = request()->routeIs(...(array) $item['active']);
                                @endphp
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold',
                                        'bg-brand-700 text-white shadow-sm' => $itemActive,
                                        'text-slate-700 hover:bg-brand-50 hover:text-brand-900 dark:text-slate-200 dark:hover:bg-white/5 dark:hover:text-white' => ! $itemActive,
                                    ])
                                    @if ($itemActive) aria-current="page" @endif
                                    x-on:click="close"
                                >
                                    <i data-lucide="{{ $item['icon'] }}" class="size-4 shrink-0"></i>
                                    <span class="min-w-0 truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>

            <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $account->name }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $account->isStaff() ? __('GIPA staff') : __('Investor') }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-white/5">
                        <i data-lucide="log-out" class="size-4"></i>
                        {{ __('Sign out') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-col lg:h-dvh lg:min-h-0 lg:overflow-y-auto">
            <div class="tricolour h-1 w-full" aria-hidden="true"></div>
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6 dark:border-slate-800 dark:bg-slate-950/95">
                <button type="button" class="inline-flex size-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 lg:hidden dark:border-slate-700 dark:text-slate-200" x-on:click="toggle" x-bind:aria-expanded="expanded" aria-controls="workspace-navigation" aria-label="{{ __('Open navigation') }}">
                    <i data-lucide="menu" class="size-5"></i>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ $account->isStaff() ? __('Administration') : __('Investor') }}</p>
                    <h1 class="truncate text-base font-bold text-slate-900 sm:text-lg dark:text-white">{{ $title }}</h1>
                </div>
                @php
                    $notificationRoute = $account->isStaff() ? route('admin.notifications') : route('investor.notifications');
                    $unreadNotifications = \App\Modules\Shared\Domain\Models\InAppNotification::query()
                        ->where('user_id', $account->id)
                        ->whereNull('read_at')
                        ->count();
                @endphp
                @if ($account->isStaff() || $account->investorProfile)
                <a href="{{ $notificationRoute }}" class="relative inline-flex size-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-white/5" aria-label="{{ __('Notifications') }}">
                    <i data-lucide="bell" class="size-5"></i>
                    @if ($unreadNotifications > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[0.65rem] font-bold text-white">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
                @endif
                <x-ui.theme-toggle />
            </header>

            <main id="workspace-content" class="flex-1 px-4 py-6 sm:px-6 sm:py-8">
                @php
                    $statusMessage = match (session('status')) {
                        'phone-verified' => $account->investorProfile
                            ? __('Your phone number is verified. Your investor account is now fully verified.')
                            : __('Your phone number is verified. Save a business profile to open the rest of the workspace.'),
                        null, '' => null,
                        default => session('status'),
                    };
                @endphp
                @if ($statusMessage)
                    <x-ui.alert type="success" class="mb-6">{{ $statusMessage }}</x-ui.alert>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    <x-assistant-widget />
</x-layouts.base>
