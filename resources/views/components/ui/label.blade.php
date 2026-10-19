@props(['for', 'required' => false])

<label for="{{ $for }}" {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-700 dark:text-slate-200']) }}>
    {{ $slot }}
    @if ($required)
        <span class="text-red-600" aria-hidden="true">*</span>
        <span class="sr-only">{{ __('(required)') }}</span>
    @endif
</label>
