@props([
    'title' => null,
    'eyebrow' => null,
    'heading' => null,
    'description' => null,
    /* Onboarding progress: null | 'account' | 'email' | 'phone' */
    'step' => null,
])

@php
    $steps = [
        'account' => __('Account'),
        'email' => __('E-mail'),
        'phone' => __('Phone'),
    ];
    $stepKeys = array_keys($steps);
    $currentIndex = $step !== null ? array_search($step, $stepKeys, true) : null;
@endphp

<x-layouts.base :title="$title">
    <a href="#auth-form" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-gold-400 focus:px-3 focus:py-2 focus:text-sm focus:font-bold focus:text-brand-950">{{ __('Skip to form') }}</a>

    <div class="flex min-h-full flex-1 flex-col lg:flex-row">
        <aside class="relative isolate flex flex-col justify-between bg-brand-900 px-4 py-6 text-white sm:px-6 sm:py-8 lg:w-[48%] lg:max-w-2xl lg:px-14 lg:py-12">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="brand-grid absolute inset-0"></div>
                <div class="absolute -right-16 bottom-[-4rem] h-[70%] w-[70%] opacity-35">
                    <x-ghana-map class="h-full w-full" />
                </div>
                <div class="absolute -left-24 -top-24 size-96 rounded-full bg-brand-700/50 blur-3xl"></div>
                <div class="absolute -bottom-32 right-0 size-80 rounded-full bg-gold-500/20 blur-3xl"></div>
            </div>

            <div class="relative flex items-center justify-between gap-4">
                <x-ui.brand variant="full" img-class="h-10 w-auto max-w-[10rem] object-contain sm:h-14 sm:max-w-[14rem] lg:h-16" class="focus-visible:ring-white focus-visible:ring-offset-brand-900" />
                <span class="hidden items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-white/90 sm:inline-flex">
                    <span class="ghana-flag-rule" aria-hidden="true"><i></i><i></i><i></i></span>
                    {{ __('Official') }}
                </span>
            </div>

            <div class="relative mt-8 max-w-lg sm:mt-12 lg:mt-0">
                @if ($eyebrow)
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-gold-400">
                        <span class="h-px w-6 bg-gold-400" aria-hidden="true"></span>
                        {{ $eyebrow }}
                    </p>
                @endif
                @if ($heading)
                    <h1 class="font-display mt-4 text-2xl font-bold leading-tight tracking-tight text-balance sm:text-4xl xl:text-5xl">{{ $heading }}</h1>
                @endif
                @if ($description)
                    <p class="mt-4 text-sm leading-relaxed text-pretty text-brand-100 sm:mt-5 sm:text-base">{{ $description }}</p>
                @endif

                <ul class="mt-10 hidden space-y-4 text-sm text-white/90 lg:block">
                    <li class="flex items-start gap-3">
                        <i data-lucide="badge-check" class="mt-0.5 size-5 shrink-0 text-gold-400"></i>
                        <span>{{ __('Opportunities reach investors only after GIPA approval and activation.') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pinned" class="mt-0.5 size-5 shrink-0 text-gold-400"></i>
                        <span>{{ __('Every record is mapped to its region and district across Ghana.') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="shield-check" class="mt-0.5 size-5 shrink-0 text-gold-400"></i>
                        <span>{{ __('Verified e-mail and phone protect your account and your enquiries.') }}</span>
                    </li>
                </ul>
            </div>

            <p class="relative mt-12 text-xs text-white/55 lg:mt-0">
                {{ __('Investment Opportunities Mapping Project · Ghana Investment Promotion Authority') }}
            </p>
        </aside>

        <div class="flex flex-1 flex-col bg-slate-50 dark:bg-slate-950">
            <div class="tricolour h-1 w-full" aria-hidden="true"></div>
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-10 sm:py-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                    <i data-lucide="arrow-left" class="size-4"></i>
                    {{ __('Back to home') }}
                </a>
                <x-ui.theme-toggle />
            </div>

            <main id="auth-form" class="flex flex-1 items-start justify-center px-4 py-6 sm:items-center sm:px-10 sm:py-8">
                <div class="w-full min-w-0 max-w-[28rem]">
                    @if ($currentIndex !== null)
                        <ol class="mb-6 flex min-w-0 items-center gap-1 sm:gap-2" aria-label="{{ __('Onboarding progress') }}">
                            @foreach ($stepKeys as $index => $key)
                                @php
                                    $state = $index < $currentIndex ? 'done' : ($index === $currentIndex ? 'current' : 'todo');
                                @endphp
                                <li class="flex min-w-0 flex-1 items-center gap-1 sm:gap-2" @if ($state === 'current') aria-current="step" @endif>
                                    <span @class([
                                        'inline-flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                        'bg-brand-700 text-white' => $state === 'done',
                                        'bg-gold-500 text-brand-950 ring-4 ring-gold-500/25' => $state === 'current',
                                        'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => $state === 'todo',
                                    ])>
                                        @if ($state === 'done')
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                            <span class="sr-only">{{ __('completed') }}</span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    <span @class([
                                        'hidden text-[0.65rem] font-semibold uppercase tracking-wide sm:inline sm:text-xs',
                                        'text-slate-900 dark:text-white' => $state !== 'todo',
                                        'text-slate-400 dark:text-slate-500' => $state === 'todo',
                                    ])>{{ $steps[$key] }}</span>
                                    @if (! $loop->last)
                                        <span @class([
                                            'h-px flex-1',
                                            'bg-brand-600' => $index < $currentIndex,
                                            'bg-slate-200 dark:bg-slate-800' => $index >= $currentIndex,
                                        ]) aria-hidden="true"></span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <footer class="px-6 py-4 text-center text-xs text-slate-500 dark:text-slate-400 sm:px-10">
                &copy; {{ date('Y') }} {{ __('Ghana Investment Promotion Authority') }} ·
                <a href="{{ route('legal.privacy') }}" class="hover:text-slate-800 dark:hover:text-slate-200">{{ __('Privacy') }}</a>
                ·
                <a href="{{ route('legal.terms') }}" class="hover:text-slate-800 dark:hover:text-slate-200">{{ __('Terms') }}</a>
                ·
                <a href="{{ route('contact.create') }}" class="hover:text-slate-800 dark:hover:text-slate-200">{{ __('Contact') }}</a>
            </footer>
        </div>
    </div>
</x-layouts.base>
