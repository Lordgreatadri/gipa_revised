<x-layouts.workspace :title="__('Business profile')">
    @php $profile = $profile ?? null; @endphp
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <form method="POST" action="{{ route('investor.profile.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Organization') }}</h2>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $profile ? __('Inquiry and interest open after a reviewer approves this profile. Identity documents are not requested until GIPA confirms the KYC set.') : __('Save this profile before the rest of the workspace opens. Inquiry and interest still wait for GIPA approval. Identity documents will be added when GIPA confirms the KYC set.') }}</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-ui.label for="organization_name" required>{{ __('Business or organization name') }}</x-ui.label>
                    <x-ui.input id="organization_name" name="organization_name" :value="old('organization_name', $profile?->organization_name)" required maxlength="160" />
                    <x-ui.input-error for="organization_name" />
                </div>
                <div>
                    <x-ui.label for="profile_type" required>{{ __('Profile type') }}</x-ui.label>
                    <select id="profile_type" name="profile_type" class="form-control" required>
                        <option value="individual" @selected(old('profile_type', $profile?->profile_type?->value ?? 'individual') === 'individual')>{{ __('Individual') }}</option>
                        <option value="organization" @selected(old('profile_type', $profile?->profile_type?->value) === 'organization')>{{ __('Organization') }}</option>
                    </select>
                </div>
                <div>
                    <x-ui.label for="country" required>{{ __('Nationality') }}</x-ui.label>
                    <select id="country" name="country" class="form-control" required>
                        <option value="">{{ __('Select a country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->uuid }}" @selected(old('country') === $country->uuid || ($profile && $profile->country_code === $country->abbreviation))>{{ $country->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="country" />
                </div>
                <div>
                    <x-ui.label for="business_email" required>{{ __('Business e-mail') }}</x-ui.label>
                    <x-ui.input id="business_email" name="business_email" type="email" :value="old('business_email', $profile?->business_email)" required />
                    <x-ui.input-error for="business_email" />
                </div>
                <div>
                    <x-ui.label for="contact_phone" required>{{ __('Business contact') }}</x-ui.label>
                    <x-ui.input id="contact_phone" name="contact_phone" :value="old('contact_phone', $profile?->contact_phone)" required placeholder="+233201234567" />
                    <x-ui.input-error for="contact_phone" />
                </div>
                <div>
                    <x-ui.label for="currency" required>{{ __('Currency') }}</x-ui.label>
                    <select id="currency" name="currency" class="form-control" required>
                        @foreach (['GHS', 'USD'] as $currency)
                            <option value="{{ $currency }}" @selected(old('currency', $profile?->currency ?? 'GHS') === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-ui.label for="preferred_language" required>{{ __('Preferred language') }}</x-ui.label>
                    <select id="preferred_language" name="preferred_language" class="form-control" required>
                        <option value="en" @selected(old('preferred_language', $profile?->preferred_language ?? 'en') === 'en')>{{ __('English') }}</option>
                        <option value="fr" @selected(old('preferred_language', $profile?->preferred_language) === 'fr')>{{ __('French') }}</option>
                    </select>
                </div>
            </div>
            <x-ui.button class="mt-6" variant="gold">{{ __('Save business profile') }}</x-ui.button>
        </form>

        <aside class="h-max rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">{{ __('Review status') }}</h2>
            @if ($profile)
                <p class="mt-3 text-lg font-bold text-slate-900 dark:text-white">{{ $profile->onboarding_state->label() }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Record status') }}: {{ $profile->status->label() }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Country code') }}: {{ $profile->country_code }}</p>
                @if ($profile->last_engaged_at)
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Last engagement') }}: {{ $profile->last_engaged_at->timezone(config('app.timezone'))->format('d M Y') }}</p>
                @endif
            @else
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('No business profile yet.') }}</p>
            @endif

            @if ($profile)
                <form method="POST" action="{{ route('investor.profile.destroy') }}" class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @csrf
                    @method('DELETE')
                    <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="consent" value="1" class="mt-1" required>
                        <span>{{ __('I consent to remove this profile. It can be removed only when there is no inquiry or interest still with GIPA.') }}</span>
                    </label>
                    <x-ui.input-error for="consent" />
                    <button type="submit" class="mt-3 text-sm font-semibold text-red-700">{{ __('Remove profile') }}</button>
                </form>
            @endif
        </aside>
    </div>
</x-layouts.workspace>
