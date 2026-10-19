@props(['tone' => 'default'])

<button
    type="button"
    x-data="themeToggle"
    x-on:click="toggle"
    x-bind:aria-pressed="pressed"
    {{ $attributes->class([
        'inline-flex size-9 shrink-0 items-center justify-center rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500',
        'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => $tone === 'default',
        'text-white/80 hover:bg-white/10 hover:text-white focus-visible:ring-white' => $tone === 'inverse',
    ]) }}
    aria-label="{{ __('Toggle dark mode') }}"
    title="{{ __('Toggle dark mode') }}"
>
    <i data-lucide="sun" class="size-5 dark:hidden"></i>
    <i data-lucide="moon" class="hidden size-5 dark:block"></i>
</button>
