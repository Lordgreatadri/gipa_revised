<x-layouts.public :title="__('Ghana investment opportunities')">
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-brand-900 text-white" aria-labelledby="hero-title">
        <div class="brand-grid pointer-events-none absolute inset-0 opacity-60" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-40 bottom-[-12rem] hidden size-[28rem] rounded-full border-[5.625rem] border-gold-500/15 sm:block" aria-hidden="true"></div>

        <div class="pointer-events-none absolute -right-[8%] top-[6%] hidden h-[86%] w-[min(49vw,38rem)] opacity-80 lg:block" aria-hidden="true">
            <x-ghana-map class="h-full w-full drop-shadow-2xl" />
            <span class="absolute left-[58%] top-[28%] size-3.5 rounded-full border-[3px] border-white bg-gold-400 shadow-[0_0_0_8px_rgba(242,201,76,.16)]"></span>
            <span class="absolute left-[46%] top-[62%] size-3.5 rounded-full border-[3px] border-white bg-gold-400 shadow-[0_0_0_8px_rgba(242,201,76,.16)]"></span>
            <span class="absolute left-[66%] top-[78%] size-3.5 rounded-full border-[3px] border-white bg-gold-400 shadow-[0_0_0_8px_rgba(242,201,76,.16)]"></span>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 pb-16 pt-10 sm:px-6 sm:pb-28 sm:pt-24 lg:pt-28">
            <p class="flex max-w-full items-start gap-3 text-[0.7rem] font-bold uppercase leading-snug tracking-wider text-brand-100 sm:items-center sm:text-xs sm:tracking-[0.18em]">
                <span class="mt-1.5 h-px w-7 shrink-0 bg-gold-400 sm:mt-0" aria-hidden="true"></span>
                <span class="min-w-0">{{ __('Ghana\'s authoritative investment opportunity platform') }}</span>
            </p>
            <h1 id="hero-title" class="font-display mt-4 max-w-3xl text-3xl font-bold leading-tight text-balance sm:mt-5 sm:text-5xl sm:leading-[1.05] lg:text-7xl">
                {{ __('Ghana\'s investment opportunities,') }}
                <span class="text-gold-400">{{ __('mapped.') }}</span>
            </h1>
            <p class="mt-5 max-w-xl text-sm leading-relaxed text-pretty text-brand-100 sm:mt-6 sm:text-lg">
                {{ __('IOMP is the Ghana Investment Promotion Authority platform where verified investors review opportunities that have been both approved and activated — by region, by district, and with GIPA standing behind the record.') }}
            </p>

            <div class="mt-8 flex w-full max-w-md flex-col gap-3 sm:mt-10 sm:max-w-none sm:flex-row sm:items-center">
                @guest
                    <a href="{{ route('register') }}" class="cta bg-gold-500 text-brand-950 shadow-lg shadow-black/10 hover:bg-gold-400">
                        <i data-lucide="user-plus" class="size-4"></i>
                        {{ __('Register as investor') }}
                    </a>
                    <a href="{{ route('login') }}" class="cta border border-white/30 text-white hover:bg-white/10">
                        {{ __('Investor sign in') }}
                    </a>
                @else
                    <a href="{{ route('investor.dashboard') }}" class="cta bg-gold-500 text-brand-950 hover:bg-gold-400">
                        {{ __('Open your dashboard') }}
                    </a>
                @endguest
            </div>

            <dl class="mt-10 grid grid-cols-1 gap-5 text-sm text-brand-100 min-[420px]:grid-cols-3 sm:mt-12 sm:gap-8">
                <div class="min-w-0">
                    <dt class="font-display text-2xl font-bold text-white">{{ __('16') }}</dt>
                    <dd>{{ __('regions') }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="font-display text-2xl font-bold text-white">{{ __('District-level') }}</dt>
                    <dd>{{ __('coverage nationwide') }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="font-display text-2xl font-bold text-white">{{ __('Verified') }}</dt>
                    <dd>{{ __('investor access only') }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Trust --}}
    <section class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950" aria-label="{{ __('Platform qualities') }}">
        <div class="mx-auto grid max-w-6xl gap-1 px-4 py-4 sm:grid-cols-2 sm:gap-2 sm:px-6 sm:py-5 lg:grid-cols-4">
            <p class="flex min-w-0 items-center gap-3 px-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <i data-lucide="shield-check" class="size-5 shrink-0 text-brand-700 dark:text-brand-300"></i>
                <span>{{ __('GIPA-reviewed records') }}</span>
            </p>
            <p class="flex min-w-0 items-center gap-3 px-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <i data-lucide="globe" class="size-5 shrink-0 text-brand-700 dark:text-brand-300"></i>
                <span>{{ __('Nationwide coverage') }}</span>
            </p>
            <p class="flex min-w-0 items-center gap-3 px-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <i data-lucide="landmark" class="size-5 shrink-0 text-brand-700 dark:text-brand-300"></i>
                <span>{{ __('Government-backed') }}</span>
            </p>
            <p class="flex min-w-0 items-center gap-3 px-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <i data-lucide="badge-check" class="size-5 shrink-0 text-brand-700 dark:text-brand-300"></i>
                <span>{{ __('Secure onboarding') }}</span>
            </p>
        </div>
    </section>

    {{-- About --}}
    <section id="about" class="scroll-mt-24 bg-slate-50 py-12 sm:py-20 dark:bg-slate-950" aria-labelledby="about-title">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('What this platform is') }}</p>
            <h2 id="about-title" class="font-display mt-3 max-w-3xl text-2xl font-bold text-balance text-slate-900 sm:text-4xl dark:text-white">{{ __('One national view of investable Ghana — for investors GIPA can trust.') }}</h2>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-pretty text-slate-600 sm:mt-5 sm:text-base dark:text-slate-300">
                {{ __('District officers capture opportunities in the field. Reviewers and administrators approve and activate them. Verified investors then see only those records that are ready for engagement — never drafts, never unapproved files, never a public dump of the pipeline.') }}
            </p>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:mt-12 sm:gap-6 md:grid-cols-3">
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-card sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-800 dark:bg-brand-900/60 dark:text-brand-200">
                        <i data-lucide="map-pinned" class="size-5"></i>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('Mapped to place') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Every opportunity sits in a region and district, so you can read Ghana geographically rather than as a list of files.') }}</p>
                </article>
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-card sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-800 dark:bg-brand-900/60 dark:text-brand-200">
                        <i data-lucide="file-check-2" class="size-5"></i>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('Approved and active') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Access is granted only when GIPA has both approved the opportunity and set it active. Knowing an identifier is never enough.') }}</p>
                </article>
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-card sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-800 dark:bg-brand-900/60 dark:text-brand-200">
                        <i data-lucide="handshake" class="size-5"></i>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('Engagement with GIPA') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Express interest or submit an inquiry. Authorized GIPA personnel review that engagement — it is not an anonymous public form.') }}</p>
                </article>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="scroll-mt-24 border-t border-slate-200 bg-white py-12 sm:py-20 dark:border-slate-800 dark:bg-slate-950" aria-labelledby="how-title">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Investor journey') }}</p>
            <h2 id="how-title" class="font-display mt-3 text-2xl font-bold text-balance text-slate-900 sm:text-4xl dark:text-white">{{ __('Four steps between this page and your dashboard.') }}</h2>
            <ol class="mt-8 grid grid-cols-1 gap-4 sm:mt-12 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                <li class="min-w-0 rounded-2xl bg-slate-50 p-5 sm:p-6 dark:bg-slate-900">
                    <span class="font-display text-3xl font-bold text-gold-600">01</span>
                    <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ __('Register') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Create an investor account and profile, with your name, e-mail, phone, country and password.') }}</p>
                </li>
                <li class="min-w-0 rounded-2xl bg-slate-50 p-5 sm:p-6 dark:bg-slate-900">
                    <span class="font-display text-3xl font-bold text-gold-600">02</span>
                    <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ __('Verify e-mail') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Open the signed link we send you. Registration alone does not unlock the catalogue.') }}</p>
                </li>
                <li class="min-w-0 rounded-2xl bg-slate-50 p-5 sm:p-6 dark:bg-slate-900">
                    <span class="font-display text-3xl font-bold text-gold-600">03</span>
                    <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ __('Verify phone') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Confirm the mobile number on your account. Both e-mail and phone must be verified.') }}</p>
                </li>
                <li class="min-w-0 rounded-2xl bg-slate-50 p-5 sm:p-6 dark:bg-slate-900">
                    <span class="font-display text-3xl font-bold text-gold-600">04</span>
                    <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ __('Review and engage') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('Use your dashboard to review approved and active opportunities, then express interest or inquire.') }}</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Access --}}
    <section id="access" class="scroll-mt-24 bg-brand-900 py-12 text-white sm:py-20" aria-labelledby="access-title">
        <div class="mx-auto grid max-w-6xl items-start gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-12">
            <div class="min-w-0">
                <p class="flex items-start gap-3 text-xs font-bold uppercase leading-snug tracking-wider text-brand-100 sm:items-center sm:tracking-[0.18em]">
                    <span class="mt-1.5 h-px w-7 shrink-0 bg-gold-400 sm:mt-0" aria-hidden="true"></span>
                    <span>{{ __('What you should expect') }}</span>
                </p>
                <h2 id="access-title" class="font-display mt-4 text-2xl font-bold text-balance sm:text-4xl">{{ __('Found an opportunity worth exploring?') }}</h2>
                <p class="mt-4 text-sm leading-relaxed text-pretty text-brand-100 sm:text-base">
                    {{ __('The public site explains the platform. Opportunity lists, maps, downloads and inquiries stay behind a verified investor account. That is intentional: GIPA reviews who you are before you see investable records.') }}
                </p>
                <ul class="mt-6 space-y-3 text-sm text-brand-50 sm:mt-8">
                    <li class="flex gap-3"><i data-lucide="check-circle-2" class="mt-0.5 size-5 shrink-0 text-gold-400"></i><span>{{ __('A private dashboard after e-mail and phone verification.') }}</span></li>
                    <li class="flex gap-3"><i data-lucide="check-circle-2" class="mt-0.5 size-5 shrink-0 text-gold-400"></i><span>{{ __('Only opportunities that are both approved and active.') }}</span></li>
                    <li class="flex gap-3"><i data-lucide="check-circle-2" class="mt-0.5 size-5 shrink-0 text-gold-400"></i><span>{{ __('Interest and inquiry as two distinct actions, reviewed by authorized GIPA personnel.') }}</span></li>
                </ul>
            </div>
            <div class="min-w-0 rounded-2xl border border-white/10 bg-white/5 p-5 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">{{ __('Ready to begin?') }}</p>
                <p class="mt-2 text-lg font-bold text-balance">{{ __('Create your investor account in a few minutes.') }}</p>
                <p class="mt-2 text-sm text-brand-100">{{ __('You will confirm your e-mail, then your phone, before the dashboard opens.') }}</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @guest
                        <a href="{{ route('register') }}" class="cta flex-1 bg-gold-500 text-brand-950 hover:bg-gold-400">{{ __('Register as investor') }}</a>
                        <a href="{{ route('login') }}" class="cta flex-1 border border-white/25 hover:bg-white/10">{{ __('Sign in') }}</a>
                    @else
                        <a href="{{ route('investor.dashboard') }}" class="cta flex-1 bg-gold-500 text-brand-950 hover:bg-gold-400">{{ __('Continue to dashboard') }}</a>
                    @endguest
                </div>
                <p class="mt-4 text-sm text-brand-100">
                    {{ __('Questions before you register?') }}
                    <a href="{{ route('contact.create') }}" class="font-semibold text-gold-400 hover:text-gold-300">{{ __('Contact us') }}</a>
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
