<x-layouts.auth
    :title="__('Verify your phone')"
    :eyebrow="__('Investor onboarding')"
    :heading="__('Last step: confirm your mobile number.')"
    :description="__('A verified phone number completes your investor profile and lets GIPA reach you about the opportunities you engage with.')"
    step="phone"
>
    <div class="flex items-start gap-4">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200">
            <i data-lucide="smartphone" class="size-6"></i>
        </span>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Verify your phone number') }}</h2>
            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Your e-mail is verified. We will text a :length-digit code to :phone. Enter it here to finish this step.', ['phone' => $phone, 'length' => $codeLength]) }}
            </p>
        </div>
    </div>

    @if (session('status') === 'phone-code-sent')
        <x-ui.alert type="success" class="mt-6">{{ __('A verification code has been sent by text message.') }}</x-ui.alert>
    @endif

    @if ($errors->any() && ! $hasActiveCode)
        <x-ui.alert type="error" class="mt-6">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="mt-8">
        @if ($hasActiveCode)
            <form method="POST" action="{{ route('phone.verification.confirm') }}" class="space-y-5" novalidate>
                @csrf
                <div>
                    <x-ui.label for="code" required>{{ __('Verification code') }}</x-ui.label>
                    <x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" maxlength="{{ $codeLength }}" required autofocus class="text-center text-2xl font-semibold tracking-[0.5em]" />
                    <x-ui.input-error for="code" />
                </div>
                <x-ui.button variant="gold" size="lg">
                    <i data-lucide="check-circle-2" class="size-4"></i>
                    {{ __('Confirm code') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('phone.verification.send') }}" class="mt-3">
                @csrf
                <x-ui.button variant="ghost">{{ __('Send a new code') }}</x-ui.button>
            </form>
        @else
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Press the button below and we will send a one-time code to your phone.') }}</p>
            <form method="POST" action="{{ route('phone.verification.send') }}" class="mt-4">
                @csrf
                <x-ui.button variant="gold" size="lg">
                    <i data-lucide="smartphone" class="size-4"></i>
                    {{ __('Send verification code') }}
                </x-ui.button>
            </form>
        @endif
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="link text-sm">{{ __('Sign out') }}</button>
    </form>
</x-layouts.auth>
