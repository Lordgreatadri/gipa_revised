@props(['title' => null])

<x-layouts.base :title="$title">
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white">
                <span class="inline-flex size-8 items-center justify-center rounded-md bg-brand-600 text-white">
                    <i data-lucide="shield-check" class="size-5"></i>
                </span>
                <span>{{ config('app.name', 'IOMP') }}</span>
            </a>

            <nav class="flex items-center gap-2" aria-label="{{ __('Primary') }}">
                @guest
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">{{ __('Register as investor') }}</a>
                @endguest
                <x-ui.theme-toggle />
            </nav>
        </div>
    </header>

    <main class="flex flex-1 items-start justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    <x-layouts.footer />
</x-layouts.base>
