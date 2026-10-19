@props(['name', 'id' => null, 'type' => 'text', 'error' => null, 'icon' => null])

@php
    $id ??= $name;
    $hasError = $errors->has($error ?? $name);
@endphp

<div class="relative mt-1.5">
    @if ($icon)
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
            <i data-lucide="{{ $icon }}" class="size-4"></i>
        </span>
    @endif
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $error ?? $name }}-error" @endif
        {{ $attributes->class(['form-control', 'pl-10' => $icon]) }}
    >
</div>
