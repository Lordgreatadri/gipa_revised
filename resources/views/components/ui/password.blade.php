@props(['name' => 'password', 'id' => null, 'label' => null, 'required' => true, 'autocomplete' => 'current-password', 'hint' => null])

@php
    $id ??= $name;
    $hasError = $errors->has($name);
@endphp

<div
    x-data="passwordField"
    data-show-label="{{ __('Show password') }}"
    data-hide-label="{{ __('Hide password') }}"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <x-ui.label :for="$id" :required="$required">{{ $label ?? __('Password') }}</x-ui.label>
        {{ $action ?? '' }}
    </div>
    <div class="relative mt-1.5">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
            <i data-lucide="lock-keyhole" class="size-4"></i>
        </span>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="password"
            x-bind:type="inputType"
            autocomplete="{{ $autocomplete }}"
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->class(['form-control pl-10 pr-11']) }}
        >
        <button
            type="button"
            x-on:click="toggle"
            x-bind:aria-pressed="pressed"
            x-bind:aria-label="toggleLabel"
            class="absolute inset-y-0 right-0 flex items-center rounded-r-lg px-3 text-slate-500 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500 dark:hover:text-slate-200"
        >
            <svg x-bind:hidden="eyeHidden" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.06 12.32c3.4-7.1 16.48-7.1 19.88 0"/><circle cx="12" cy="12" r="3"/></svg>
            <svg x-bind:hidden="eyeOffHidden" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" x-cloak><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.1A11 11 0 0 1 12 5c7 0 10 7 10 7a18 18 0 0 1-2.3 3.3"/><path d="M6.6 6.6A18 18 0 0 0 2 12s3 7 10 7a11 11 0 0 0 4.1-.8"/></svg>
        </button>
    </div>
    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    <x-ui.input-error :for="$name" />
</div>
