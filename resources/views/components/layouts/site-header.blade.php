@props(['inverse' => false])

<header
    @class([
        'sticky top-0 z-40 border-b backdrop-blur-xl',
        'border-white/10 bg-brand-950' => $inverse,
        'border-slate-200/80 bg-white/90 dark:border-slate-800 dark:bg-slate-950/90' => ! $inverse,
    ])
    x-data="navMenu"
>
    <div class="mx-auto flex max-w-6xl min-w-0 items-center gap-2 px-4 py-3 sm:gap-3 sm:px-6">
        <x-ui.brand
            variant="mark"
            :inverted="$inverse"
            img-class="h-8 w-auto max-h-8 max-w-[4.5rem] shrink-0 object-contain sm:h-10 sm:max-h-10 sm:max-w-[7.5rem]"
            class="min-w-0 flex-1 {{ $inverse ? 'focus-visible:ring-white focus-visible:ring-offset-brand-950' : '' }}"
        />

        <nav class="hidden items-center gap-6 text-sm font-semibold lg:flex" aria-label="{{ __('Primary') }}">
            <a href="{{ route('home') }}#about" @class(['hover:text-white' => $inverse, 'text-white/80' => $inverse, 'text-slate-700 hover:text-brand-800 dark:text-slate-200 dark:hover:text-white' => ! $inverse])>{{ __('The platform') }}</a>
            <a href="{{ route('home') }}#how-it-works" @class(['hover:text-white' => $inverse, 'text-white/80' => $inverse, 'text-slate-700 hover:text-brand-800 dark:text-slate-200 dark:hover:text-white' => ! $inverse])>{{ __('How it works') }}</a>
            <a href="{{ route('home') }}#access" @class(['hover:text-white' => $inverse, 'text-white/80' => $inverse, 'text-slate-700 hover:text-brand-800 dark:text-slate-200 dark:hover:text-white' => ! $inverse])>{{ __('Investor access') }}</a>
            <a href="{{ route('contact.create') }}" @class(['hover:text-white' => $inverse, 'text-white/80' => $inverse, 'text-slate-700 hover:text-brand-800 dark:text-slate-200 dark:hover:text-white' => ! $inverse])>{{ __('Contact us') }}</a>
            <a href="{{ route('platform.guide') }}" @class(['hover:text-white' => $inverse, 'text-white/80' => $inverse, 'text-slate-700 hover:text-brand-800 dark:text-slate-200 dark:hover:text-white' => ! $inverse])>{{ __('Guide') }}</a>
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-1.5 sm:gap-2">
            <x-ui.theme-toggle :tone="$inverse ? 'inverse' : 'default'" />
            @guest
                <a href="{{ route('login') }}" @class([
                    'hidden rounded-lg px-3 py-2 text-sm font-semibold md:inline-flex',
                    'text-white/90 hover:bg-white/10' => $inverse,
                    'text-brand-800 hover:bg-brand-50 dark:text-brand-200 dark:hover:bg-white/10' => ! $inverse,
                ])>{{ __('Sign in') }}</a>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-gold-500 px-2.5 py-2 text-xs font-bold text-brand-950 hover:bg-gold-400 sm:px-3.5 sm:text-sm">
                    <span class="sm:hidden">{{ __('Register') }}</span>
                    <span class="hidden sm:inline">{{ __('Register as investor') }}</span>
                </a>
            @else
                @if (auth()->user()->isInvestor())
                    <a href="{{ route('investor.dashboard') }}" class="inline-flex items-center rounded-lg bg-gold-500 px-2.5 py-2 text-xs font-bold text-brand-950 hover:bg-gold-400 sm:px-3.5 sm:text-sm">{{ __('Workspace') }}</a>
                @elseif (auth()->user()->can('admin.dashboard.view'))
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg bg-gold-500 px-2.5 py-2 text-xs font-bold text-brand-950 hover:bg-gold-400 sm:px-3.5 sm:text-sm">{{ __('Workspace') }}</a>
                @endif
            @endguest
            <button
                type="button"
                @class([
                    'inline-flex size-10 items-center justify-center rounded-lg lg:hidden',
                    'border border-white/20 text-white' => $inverse,
                    'border border-slate-200 text-slate-700 dark:border-slate-700 dark:text-slate-200' => ! $inverse,
                ])
                x-on:click="toggle"
                x-bind:aria-expanded="expanded"
                aria-controls="mobile-navigation"
                aria-label="{{ __('Open navigation') }}"
            >
                <svg x-bind:hidden="open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/></svg>
                <svg x-bind:hidden="isClosed" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" x-cloak><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <nav
        id="mobile-navigation"
        @class([
            'px-4 py-3 lg:hidden',
            'border-t border-white/10 bg-brand-950' => $inverse,
            'border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950' => ! $inverse,
        ])
        x-bind:hidden="isClosed"
        x-cloak
        aria-label="{{ __('Mobile') }}"
    >
        <a href="{{ route('home') }}#about" @class(['block py-2.5 font-semibold', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse]) x-on:click="close">{{ __('The platform') }}</a>
        <a href="{{ route('home') }}#how-it-works" @class(['block py-2.5 font-semibold', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse]) x-on:click="close">{{ __('How it works') }}</a>
        <a href="{{ route('home') }}#access" @class(['block py-2.5 font-semibold', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse]) x-on:click="close">{{ __('Investor access') }}</a>
        <a href="{{ route('contact.create') }}" @class(['block py-2.5 font-semibold', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse]) x-on:click="close">{{ __('Contact us') }}</a>
        <a href="{{ route('platform.guide') }}" @class(['block py-2.5 font-semibold', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse]) x-on:click="close">{{ __('Guide') }}</a>
        @guest
            <a href="{{ route('login') }}" @class(['block py-2.5 font-semibold md:hidden', 'text-white' => $inverse, 'text-slate-800 dark:text-slate-100' => ! $inverse])>{{ __('Sign in') }}</a>
        @endguest
    </nav>
</header>
