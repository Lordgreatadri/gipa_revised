<x-layouts.public :title="$title" :description="$summary">
    <section class="border-b border-slate-200 bg-brand-900 py-12 text-white sm:py-16 dark:border-slate-800">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-brand-100" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white" aria-current="page">{{ $title }}</span>
            </nav>
            <h1 class="font-display mt-6 text-3xl font-bold text-balance sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 text-sm leading-relaxed text-pretty text-brand-100 sm:text-base">{{ $summary }}</p>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16">
        <p class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
            {{ __('This wording is a placeholder. It is not the approved GIPA policy. Do not treat it as legal advice or as a published commitment.') }}
        </p>
        <div class="mt-8 space-y-6 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
            <p>{{ __('Until the approved document is published, IOMP uses personal data only to operate your account, send verification messages, and respond to enquiries you submit through this site.') }}</p>
            <p>{{ __('Opportunity records remain behind a verified investor account. Staff join by invitation. Passwords, one-time codes and payment details must never be sent through the contact form.') }}</p>
            <p>
                {{ __('Questions about this page can be sent through') }}
                <a href="{{ route('contact.create') }}" class="link">{{ __('Contact us') }}</a>.
            </p>
        </div>
    </article>
</x-layouts.public>
