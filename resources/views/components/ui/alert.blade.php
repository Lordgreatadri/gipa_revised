@props(['type' => 'info', 'title' => null])

<div
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    {{ $attributes->class([
        'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm',
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-100' => $type === 'success',
        'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900 dark:bg-sky-950/60 dark:text-sky-100' => $type === 'info',
        'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/60 dark:text-amber-100' => $type === 'warning',
        'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950/60 dark:text-red-100' => $type === 'error',
    ]) }}
>
    <i data-lucide="{{ $type === 'success' ? 'check-circle-2' : 'alert-circle' }}" class="mt-0.5 size-4 shrink-0"></i>
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5' => $title])>{{ $slot }}</div>
    </div>
</div>
