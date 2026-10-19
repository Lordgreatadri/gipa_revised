@props(['title' => null, 'description' => null])

<x-layouts.base
    :title="$title"
    :description="$description ?? __('Ghana\'s official platform for verified investors to review approved and active investment opportunities.')"
>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-gold-400 focus:px-3 focus:py-2 focus:text-sm focus:font-bold focus:text-brand-950">{{ __('Skip to main content') }}</a>

    <div class="bg-brand-950 text-[11px] font-medium text-brand-100">
        <div class="mx-auto flex max-w-6xl items-start gap-2 px-4 py-2 sm:items-center sm:px-6">
            <span class="ghana-flag-rule mt-1 shrink-0 sm:mt-0" aria-hidden="true"><i></i><i></i><i></i></span>
            <span class="min-w-0 leading-snug tracking-wide sm:tracking-[0.14em] sm:uppercase">{{ __('An official investment platform of the Republic of Ghana') }}</span>
            <span class="ml-auto hidden shrink-0 items-center gap-1.5 sm:inline-flex">
                <span class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,0.18)]" aria-hidden="true"></span>
                {{ __('Platform online') }}
            </span>
        </div>
    </div>
    <div class="tricolour h-0.5 w-full" aria-hidden="true"></div>

    <x-layouts.site-header :inverse="true" />

    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    <x-layouts.site-footer />
    <x-assistant-widget />
</x-layouts.base>
