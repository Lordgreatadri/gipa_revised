<x-layouts.auth
    :title="__('Choose a new password')"
    :eyebrow="__('Account recovery')"
    :heading="__('Set a new password.')"
    :description="__('Choose a strong password to restore secure access to your IOMP account.')"
>
    <div class="mb-8 flex items-start gap-4">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200">
            <i data-lucide="lock-keyhole" class="size-6"></i>
        </span>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Choose a new password') }}</h2>
            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">{{ __('Use at least 12 characters. A mix of letters, numbers and symbols is strongest.') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-ui.label for="email" required>{{ __('E-mail address') }}</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email', $email)" required autofocus autocomplete="username" inputmode="email" />
            <x-ui.input-error for="email" />
        </div>

        <x-ui.password name="password" :label="__('New password')" autocomplete="new-password" />
        <x-ui.password name="password_confirmation" :label="__('Confirm new password')" autocomplete="new-password" />

        <x-ui.button variant="gold" size="lg">
            <i data-lucide="key-round" class="size-4"></i>
            {{ __('Update password') }}
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
        <a href="{{ route('login') }}" class="link inline-flex items-center gap-1">
            <i data-lucide="arrow-left" class="size-4"></i>
            {{ __('Return to sign in') }}
        </a>
    </p>
</x-layouts.auth>
