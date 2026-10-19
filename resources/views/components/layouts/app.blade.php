@props(['title' => null])

<x-layouts.base :title="$title">
    <div class="tricolour h-1 w-full" aria-hidden="true"></div>
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <x-ui.brand variant="mark" img-class="h-10 w-auto max-h-10 max-w-[7.5rem] shrink-0 object-contain" />

            <div class="flex items-center gap-3">
                @auth
                    <span class="hidden text-sm font-medium text-slate-600 dark:text-slate-300 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                            <i data-lucide="log-out" class="size-4"></i>
                            <span>{{ __('Sign out') }}</span>
                        </button>
                    </form>
                @endauth
                <x-ui.theme-toggle />
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
        {{ $slot }}
    </main>

    <x-layouts.site-footer />
</x-layouts.base>
