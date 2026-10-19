@props([
    'name',
    'countries',           // iterable of Country models (uuid, name, code, icon_url)
    'id' => null,
    'value' => null,       // selected uuid
    'display' => 'name',   // 'name' => residence country, 'code' => dial code
    'placeholder' => null,
    'error' => null,
    'label' => null,       // visible label id for aria-labelledby
])

@php
    $id ??= $name;
    $errorKey = $error ?? $name;
    $hasError = $errors->has($errorKey);
    $options = collect($countries)->map(fn ($country) => [
        'value' => $country->uuid,
        'name' => $country->name,
        'code' => $country->code,
        'abbreviation' => $country->abbreviation,
        'flag' => filled($country->icon_url) ? asset(ltrim((string) $country->icon_url, '/')) : null,
    ])->values();
    $placeholder ??= $display === 'code' ? __('Code') : __('Select a country');
@endphp

<div
    x-data="countryPicker"
    x-modelable="value"
    x-on:keydown.escape.prevent.stop="dismiss"
    x-on:click.outside="dismiss"
    x-on:focusout="onFocusOut"
    data-display="{{ $display }}"
    data-initial="{{ (string) $value }}"
    data-countries='@json($options)'
    x-bind:class="stackClass"
    {{ $attributes->class(['relative min-w-0']) }}
>
    <input type="hidden" name="{{ $name }}" x-bind:value="value">

    <button
        type="button"
        id="{{ $id }}"
        x-ref="trigger"
        x-on:click="toggle"
        x-on:keydown.arrow-down.prevent="show"
        x-on:keydown.arrow-up.prevent="show"
        x-bind:aria-expanded="expanded"
        aria-haspopup="listbox"
        aria-controls="{{ $id }}-listbox"
        @if ($label) aria-labelledby="{{ $label }}" @endif
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $errorKey }}-error" @endif
        @class(['form-control flex min-w-0 items-center gap-2 text-left', 'pr-9' => true])
    >
        <template x-if="hasSelection">
            <span class="flex min-w-0 items-center gap-2">
                <img x-bind:src="selected.flag" alt="" x-bind:hidden="selectedFlagHidden" class="h-5 w-7 shrink-0 rounded-[3px] object-cover ring-1 ring-slate-900/15" width="28" height="20" loading="lazy">
                <span
                    x-bind:hidden="selectedAbbrevHidden"
                    class="inline-flex h-5 w-7 shrink-0 items-center justify-center rounded-[3px] bg-slate-100 text-[10px] font-bold text-slate-600 ring-1 ring-slate-900/10 dark:bg-slate-800 dark:text-slate-200"
                    x-text="selected.abbreviation"
                ></span>
                <span class="truncate font-semibold tabular-nums" x-text="selectedLabel"></span>
            </span>
        </template>
        <template x-if="noSelection">
            <span class="truncate text-slate-400 dark:text-slate-500">{{ $placeholder }}</span>
        </template>
        <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div
        x-bind:hidden="panelHidden"
        x-cloak
        class="absolute left-0 z-50 mt-1.5 w-[min(100vw-2rem,20rem)] max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg sm:max-w-none dark:border-slate-700 dark:bg-slate-900 {{ $display === 'code' ? 'sm:w-max' : 'w-full' }}"
    >
        <div class="border-b border-slate-100 p-2 dark:border-slate-800">
            <input
                type="search"
                x-ref="search"
                x-model="query"
                x-on:input="resetActive"
                x-on:keydown.arrow-down.prevent="moveDown"
                x-on:keydown.arrow-up.prevent="moveUp"
                x-on:keydown.enter.prevent="chooseActive"
                x-on:keydown.tab="dismiss"
                class="form-control py-2"
                placeholder="{{ __('Search country or code…') }}"
                aria-label="{{ __('Search countries') }}"
                autocomplete="off"
            >
        </div>
        <ul
            id="{{ $id }}-listbox"
            x-ref="list"
            role="listbox"
            class="max-h-64 overflow-y-auto py-1 text-sm"
            tabindex="-1"
        >
            <template x-for="country in filtered" :key="country.value">
                <li
                    role="option"
                    x-bind:aria-selected="optionSelected(country)"
                    x-bind:data-active="optionActive(country)"
                    x-on:click="choose(country)"
                    x-on:mousemove="setActive(country)"
                    class="flex cursor-pointer items-center gap-3 px-3 py-2 text-slate-800 dark:text-slate-100"
                    x-bind:class="optionClass(country)"
                >
                    <img x-bind:src="country.flag" alt="" x-bind:hidden="flagHidden(country)" class="h-5 w-7 shrink-0 rounded-[3px] object-cover ring-1 ring-slate-900/15" width="28" height="20" loading="lazy">
                    <span
                        x-bind:hidden="abbrevHidden(country)"
                        class="inline-flex h-5 w-7 shrink-0 items-center justify-center rounded-[3px] bg-slate-100 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-200"
                        x-text="country.abbreviation"
                    ></span>
                    <span class="min-w-0 flex-1 truncate" x-text="country.name"></span>
                    <span class="shrink-0 font-semibold tabular-nums text-slate-600 dark:text-slate-300" x-text="country.code"></span>
                </li>
            </template>
            <li x-bind:hidden="hasMatches" class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ __('No countries match your search.') }}</li>
        </ul>
    </div>
</div>
