@props(['title' => null, 'description' => null, 'heading' => 'h1'])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900 sm:p-8']) }}>
    @if ($title)
        <{{ $heading }} class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</{{ $heading }}>
    @endif
    @if ($description)
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">{{ $description }}</p>
    @endif
    <div @class(['mt-6' => $title || $description])>
        {{ $slot }}
    </div>
</section>
