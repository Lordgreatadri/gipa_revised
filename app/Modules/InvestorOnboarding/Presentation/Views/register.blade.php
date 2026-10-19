<x-layouts.auth
    :title="__('Register as investor')"
    :eyebrow="__('Investor onboarding')"
    :heading="__('Create your investor account.')"
    :description="__('Three short steps: create your account, confirm your e-mail address, then confirm your mobile number. Verified investors gain access to approved and active opportunities.')"
    step="account"
>
    <div class="mb-8">
        <h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">{{ __('Create your investor account') }}</h2>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">{{ __('After registering you will verify your e-mail address and phone number before accessing opportunities.') }}</p>
    </div>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6" :title="__('Please review the highlighted fields.')">
            {{ __('Your account has not been created yet.') }}
        </x-ui.alert>
    @endif

    <form
        method="POST"
        action="{{ route('register.store') }}"
        class="space-y-5"
        novalidate
        x-data="registerForm"
        x-on:country-changed="onCountryChanged"
        data-country="{{ (string) old('country', $defaultCountry ?? '') }}"
        data-phone-country="{{ (string) old('phone_country', $defaultPhoneCountry ?? '') }}"
        data-phone-touched="{{ old('phone_country') !== null ? '1' : '0' }}"
    >
        @csrf

        <div>
            <x-ui.label for="name" required>{{ __('Full name') }}</x-ui.label>
            <x-ui.input name="name" icon="users" :value="old('name')" required autofocus autocomplete="name" maxlength="150" placeholder="{{ __('As shown on your identification') }}" />
            <x-ui.input-error for="name" />
        </div>

        <div>
            <x-ui.label for="email" required>{{ __('E-mail address') }}</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email')" required autocomplete="email" inputmode="email" placeholder="you@example.com" />
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('A verification link will be sent to this address.') }}</p>
            <x-ui.input-error for="email" />
        </div>

        <div>
            <x-ui.label for="country" id="country-label" required>{{ __('Country of residence') }}</x-ui.label>
            <div class="mt-1.5">
            <x-ui.country-picker name="country" :countries="$countries" :value="old('country', $defaultCountry ?? null)" label="country-label" x-model="country" />
            </div>
            <x-ui.input-error for="country" />
        </div>

        <div>
            <x-ui.label for="phone_number" required>{{ __('Mobile phone number') }}</x-ui.label>
            <x-ui.phone-input
                :countries="$countries"
                :country="old('phone_country', $defaultPhoneCountry ?? null)"
                :number="old('phone_number')"
                x-model="phoneCountry"
                :hint="__('Choose the country code (flag and dial code), then enter the number without the leading 0. A verification code will be sent to this number.')"
            />
        </div>

        <div class="grid min-w-0 gap-5 sm:grid-cols-2">
            <x-ui.password name="password" :label="__('Password')" autocomplete="new-password" />
            <x-ui.password name="password_confirmation" :label="__('Confirm password')" autocomplete="new-password" />
        </div>
        <p class="-mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('Use at least 12 characters. A mix of letters, numbers and symbols is strongest.') }}</p>

        <div>
            <div class="flex items-start gap-3">
                <input id="terms" name="terms" type="checkbox" value="1" required class="form-check mt-0.5" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                <label for="terms" class="text-sm text-slate-700 dark:text-slate-200">
                    {{ __('I accept the') }}
                    <a href="{{ route('legal.terms') }}" class="link" target="_blank" rel="noopener">{{ __('terms of use') }}</a>
                    {{ __('and the') }}
                    <a href="{{ route('legal.privacy') }}" class="link" target="_blank" rel="noopener">{{ __('privacy notice') }}</a>
                    {{ __('for investor onboarding.') }}
                </label>
            </div>
            <x-ui.input-error for="terms" />
        </div>

        <x-ui.button variant="gold" size="lg">
            <i data-lucide="user-plus" class="size-4"></i>
            {{ __('Create secure account') }}
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="link">{{ __('Sign in') }}</a>
    </p>
</x-layouts.auth>
