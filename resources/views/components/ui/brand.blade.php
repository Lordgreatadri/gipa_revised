@props([
    /* `mark`  : compact GIPA logo + IOMP wordmark (headers, footer).
       `full`  : the complete GIPA lockup; best on dark surfaces. */
    'variant' => 'mark',
    'href' => null,
    'wordmark' => true,
    'imgClass' => null,
    'inverted' => false,
])

@php
    $href ??= route('home');
    $wordmarkTitle = $inverted
        ? 'block text-base font-bold tracking-tight text-white'
        : 'block text-base font-bold tracking-tight text-slate-900 dark:text-white';
    $wordmarkSub = $inverted
        ? 'block text-[11px] font-medium uppercase tracking-wider text-white/70'
        : 'block text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex min-w-0 items-center gap-2 rounded-md sm:gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950']) }} aria-label="{{ config('app.name', 'IOMP') }} — {{ __('home') }}">
    @if ($variant === 'full')
        <img
            src="{{ asset('images/gipa-logo.png') }}"
            alt="{{ __('Ghana Investment Promotion Authority') }}"
            class="{{ $imgClass ?? 'h-14 w-auto max-w-[16rem] object-contain' }}"
            width="180"
            height="100"
            decoding="async"
        >
    @else
        <img
            src="{{ asset('images/gipa-logo.png') }}"
            alt="{{ __('Ghana Investment Promotion Authority') }}"
            class="{{ $imgClass ?? 'h-10 w-auto max-h-10 max-w-[7.5rem] shrink-0 object-contain' }}"
            width="120"
            height="40"
            decoding="async"
        >
        @if ($wordmark)
            <span class="min-w-0 leading-tight">
                <span class="{{ $wordmarkTitle }}">{{ config('app.name', 'IOMP') }}</span>
                <span class="{{ $wordmarkSub }} hidden md:block">{{ __('Investment Opportunities Mapping') }}</span>
            </span>
        @endif
    @endif
</a>
