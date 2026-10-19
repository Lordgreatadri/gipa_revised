<x-layouts.auth
    :title="__('Sign in')"
    :eyebrow="__('Secure access')"
    :heading="__('Welcome back to Ghana\'s investment map.')"
    :description="__('Sign in to continue your onboarding or to explore approved and active investment opportunities across the regions and districts of Ghana.')"
>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Sign in') }}</h2>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">{{ __('Use the e-mail address and password of your IOMP account.') }}</p>
    </div>

    @if (session('status') === 'password-reset')
        <x-ui.alert type="success" class="mb-6">{{ __('Your password has been reset. Sign in with your new password.') }}</x-ui.alert>
    @elseif (session('status'))
        <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <x-ui.label for="email" required>{{ __('E-mail address') }}</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email')" required autofocus autocomplete="username" inputmode="email" placeholder="you@example.com" />
            <x-ui.input-error for="email" />
        </div>

        <x-ui.password name="password" autocomplete="current-password">
            <x-slot:action>
                <a href="{{ route('password.request') }}" class="link text-sm">{{ __('Forgot password?') }}</a>
            </x-slot:action>
        </x-ui.password>

        <div class="flex items-center gap-2">
            <input id="remember" name="remember" type="checkbox" value="1" class="form-check" @checked(old('remember'))>
            <label for="remember" class="text-sm text-slate-700 dark:text-slate-200">{{ __('Keep me signed in on this device') }}</label>
        </div>

        <x-ui.button variant="gold" size="lg">
            <i data-lucide="log-in" class="size-4"></i>
            {{ __('Sign in securely') }}
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
        {{ __('New investor?') }}
        <a href="{{ route('register') }}" class="link">{{ __('Create an account') }}</a>
    </p>
    {{-- <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">
        {{ __('GIPA and district personnel receive their access by invitation and cannot self-register.') }}
    </p> --}}
</x-layouts.auth>
