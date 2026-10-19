<x-layouts.auth
    :title="__('Verify your e-mail')"
    :eyebrow="__('Investor onboarding')"
    :heading="__('One click to confirm your e-mail.')"
    :description="__('We use your verified e-mail address to keep you informed about your enquiries and to protect access to your account.')"
    step="email"
>
    <div class="flex items-start gap-4">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200">
            <i data-lucide="mail-check" class="size-6"></i>
        </span>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Verify your e-mail address') }}</h2>
            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                {{ __('We sent a verification link to :email. Open it to confirm your e-mail address. Phone verification follows as the next step.', ['email' => auth()->user()->email]) }}
            </p>
        </div>
    </div>

    @if (session('status') === 'verification-link-sent')
        <x-ui.alert type="success" class="mt-6">{{ __('A new verification link has been sent.') }}</x-ui.alert>
    @endif

    <p class="mt-8 text-sm text-slate-600 dark:text-slate-300">{{ __("Didn't receive the message? Check your spam folder, or request a new link below.") }}</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
        @csrf
        <x-ui.button variant="gold">
            <i data-lucide="mail" class="size-4"></i>
            {{ __('Resend verification e-mail') }}
        </x-ui.button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="link text-sm">{{ __('Sign out') }}</button>
    </form>
</x-layouts.auth>
