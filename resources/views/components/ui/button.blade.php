@props(['type' => 'submit', 'variant' => 'primary', 'size' => 'md'])

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex w-full items-center justify-center gap-2 rounded-lg font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 disabled:cursor-not-allowed disabled:opacity-60',
        'px-4 py-2.5 text-sm' => $size === 'md',
        'px-5 py-3 text-base' => $size === 'lg',
        'bg-brand-700 text-white shadow-sm hover:bg-brand-800 focus-visible:ring-brand-500' => $variant === 'primary',
        'bg-gold-500 text-brand-950 shadow-sm hover:bg-gold-400 focus-visible:ring-gold-500' => $variant === 'gold',
        'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 focus-visible:ring-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800' => $variant === 'secondary',
        'text-slate-700 hover:bg-slate-100 focus-visible:ring-slate-400 dark:text-slate-200 dark:hover:bg-slate-800' => $variant === 'ghost',
    ]) }}
>
    {{ $slot }}
</button>
