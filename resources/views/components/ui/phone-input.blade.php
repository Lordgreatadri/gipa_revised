@props([
    'countries',
    'countryName' => 'phone_country',
    'numberName' => 'phone_number',
    'country' => null,      // selected dial-code country uuid
    'number' => null,       // national number
    'error' => 'phone',     // server-side error key for the composed E.164 number
    'hint' => null,
])

@php
    $hasError = $errors->has($error) || $errors->has($numberName) || $errors->has($countryName);
@endphp

{{-- `dial` is exposed through x-modelable so a parent form may bind it with x-model. --}}
<div x-data="phoneInput" x-modelable="dial" data-phone-input data-dial="{{ (string) $country }}" {{ $attributes }}>
    <div class="mt-1.5 flex min-w-0 flex-col gap-2 min-[420px]:flex-row">
        <x-ui.country-picker
            :name="$countryName"
            :id="$countryName"
            :countries="$countries"
            :value="$country"
            display="code"
            :error="$error"
            x-model="dial"
            class="w-full min-[420px]:w-[8.5rem] min-[420px]:shrink-0 sm:w-[9.75rem]"
        />
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                <i data-lucide="smartphone" class="size-4"></i>
            </span>
            <input
                id="{{ $numberName }}"
                name="{{ $numberName }}"
                type="tel"
                value="{{ $number }}"
                inputmode="tel"
                autocomplete="tel-national"
                placeholder="{{ __('20 123 4567') }}"
                required
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $error }}-error" @endif
                class="form-control pl-10"
            >
        </div>
    </div>
    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    <x-ui.input-error :for="$error" />
    @if ($error !== $numberName)
        <x-ui.input-error :for="$numberName" />
    @endif
    @if ($error !== $countryName)
        <x-ui.input-error :for="$countryName" />
    @endif
</div>
