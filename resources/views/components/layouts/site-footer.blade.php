<footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 sm:py-12 md:grid-cols-4 md:gap-10">
        <div class="md:col-span-2">
            <x-ui.brand variant="mark" img-class="h-11 w-auto max-h-11 max-w-[8rem] shrink-0 object-contain" />
            <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('The Investment Opportunities Mapping Platform is Ghana\'s official channel for verified investors to review opportunities that GIPA has approved and activated.') }}
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Explore') }}</p>
            <nav class="mt-3 grid gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                <a href="{{ route('home') }}#about">{{ __('The platform') }}</a>
                <a href="{{ route('home') }}#how-it-works">{{ __('How it works') }}</a>
                <a href="{{ route('home') }}#access">{{ __('Investor access') }}</a>
                <a href="{{ route('contact.create') }}">{{ __('Contact us') }}</a>
            </nav>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Accounts') }}</p>
            <nav class="mt-3 grid gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                @guest
                    <a href="{{ route('register') }}">{{ __('Register as investor') }}</a>
                    <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
                @else
                    @if (auth()->user()->isInvestor())
                        <a href="{{ route('investor.dashboard') }}">{{ __('Investor dashboard') }}</a>
                    @elseif (auth()->user()->can('admin.dashboard.view'))
                        <a href="{{ route('admin.dashboard') }}">{{ __('Administration') }}</a>
                    @endif
                @endguest
                {{-- <p class="pt-2 text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('GIPA staff join by invitation. They cannot self-register.') }}</p> --}}
            </nav>
        </div>
    </div>
    <div class="border-t border-slate-200 dark:border-slate-800">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:text-slate-400">
            <span>&copy; {{ date('Y') }} {{ __('Ghana Investment Promotion Authority') }} · {{ __('Republic of Ghana') }}</span>
            <nav class="flex flex-wrap gap-x-4 gap-y-1" aria-label="{{ __('Legal') }}">
                <a href="{{ route('legal.privacy') }}" class="hover:text-slate-800 dark:hover:text-white">{{ __('Privacy') }}</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-slate-800 dark:hover:text-white">{{ __('Terms') }}</a>
                <a href="{{ route('legal.data-policy') }}" class="hover:text-slate-800 dark:hover:text-white">{{ __('Data policy') }}</a>
                <a href="{{ route('platform.guide') }}" class="hover:text-slate-800 dark:hover:text-white">{{ __('User guide') }}</a>
                <a href="{{ route('api.documentation') }}" class="hover:text-slate-800 dark:hover:text-white">{{ __('API Doc') }}</a>
            </nav>
        </div>
    </div>
</footer>
