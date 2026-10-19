<x-layouts.auth
    :title="__('Reset password')"
    :eyebrow="__('Account recovery')"
    :heading="__('Recover secure access.')"
    :description="__('Request a time-limited link to choose a new password for your investor account.')"
>
    <div class="mb-8 flex items-start gap-4">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200">
            <i data-lucide="key-round" class="size-6"></i>
        </span>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Forgot your password?') }}</h2>
            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Enter your registered e-mail address to receive a password reset link.') }}
            </p>
        </div>
    </div>

    @if (session('status') === 'reset-link-sent')
        <x-ui.alert type="success" class="mb-6" :title="__('Check your inbox')">
            {{ __('If an account exists for that address, a password reset link is on its way. The link expires after :minutes minutes.', ['minutes' => $expireMinutes]) }}
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <x-ui.label for="email" required>{{ __('E-mail address') }}</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email')" required autofocus autocomplete="email" inputmode="email" placeholder="you@example.com" />
            <x-ui.input-error for="email" />
        </div>

        <x-ui.button variant="gold" size="lg">
            <i data-lucide="mail" class="size-4"></i>
            {{ __('Send reset link') }}
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
        <a href="{{ route('login') }}" class="link inline-flex items-center gap-1">
            <i data-lucide="arrow-left" class="size-4"></i>
            {{ __('Return to sign in') }}
        </a>
    </p>
</x-layouts.auth>
