<x-layouts.public :title="__('Contact us')" :description="__('Contact the Ghana Investment Promotion Authority about investor onboarding, the platform, or an enquiry that is not yet tied to a verified opportunity.')">
    <section class="relative isolate overflow-hidden bg-brand-900 py-12 text-white sm:py-16">
        <div class="brand-grid pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-brand-100" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white" aria-current="page">{{ __('Contact us') }}</span>
            </nav>
            <p class="mt-6 inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-gold-400">{{ __('Investment services') }}</p>
            <h1 class="font-display mt-4 max-w-3xl text-3xl font-bold text-balance sm:text-5xl">{{ __('Contact us') }}</h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-pretty text-brand-100 sm:text-base">
                {{ __('Tell us what you need and our investment services team will get back to you. You will receive an email confirming that we have your enquiry. Do not send passwords, one-time codes, or payment details.') }}
            </p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-5">
        <div class="min-w-0 lg:col-span-2">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Before you write') }}</p>
            <h2 class="font-display mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ __('You may find your answer faster here') }}</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Opportunity lists stay behind a verified investor account. These pages explain the public journey.') }}</p>

            <ol class="mt-6 space-y-3">
                <li>
                    <a href="{{ route('home') }}#how-it-works" class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-card hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900">
                        <span class="font-display text-xl font-bold text-gold-600">1</span>
                        <span>
                            <strong class="block text-slate-900 dark:text-white">{{ __('How investor access works') }}</strong>
                            <small class="text-slate-600 dark:text-slate-300">{{ __('Register, verify e-mail and phone and create business profile, then review approved and active records.') }}</small>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('platform.guide') }}" class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-card hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900">
                        <span class="font-display text-xl font-bold text-gold-600">2</span>
                        <span>
                            <strong class="block text-slate-900 dark:text-white">{{ __('Read the user guide') }}</strong>
                            <small class="text-slate-600 dark:text-slate-300">{{ __('A short outline of registration, onboarding and what stays private.') }}</small>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('register') }}" class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-card hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900">
                        <span class="font-display text-xl font-bold text-gold-600">3</span>
                        <span>
                            <strong class="block text-slate-900 dark:text-white">{{ __('Create an investor account') }}</strong>
                            <small class="text-slate-600 dark:text-slate-300">{{ __('Create a business profile, subject to verification.') }}</small>
                        </span>
                    </a>
                </li>
            </ol>

            <p class="mt-10 text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Other channels') }}</p>
            <h2 class="font-display mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Reach the team directly') }}</h2>

            <section class="mt-5 overflow-hidden rounded-3xl border {{ $office['open'] ? 'border-emerald-200 dark:border-emerald-900' : 'border-slate-200 dark:border-slate-800' }} bg-white shadow-card dark:bg-slate-900" aria-label="{{ __('Office hours') }}">
                <div class="flex items-start justify-between gap-4 px-5 py-4 {{ $office['open'] ? 'bg-emerald-50 dark:bg-emerald-950/40' : 'bg-slate-50 dark:bg-slate-950' }}">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] {{ $office['open'] ? 'text-emerald-800 dark:text-emerald-200' : 'text-slate-500' }}">{{ __('Office hours') }} · {{ __('Africa/Accra') }}</p>
                        <p class="font-display mt-1 text-3xl font-bold {{ $office['open'] ? 'text-emerald-950 dark:text-emerald-50' : 'text-slate-900 dark:text-white' }}">{{ __($office['headline']) }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ __($office['detail']) }}</p>
                    </div>
                    <span class="mt-1 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $office['open'] ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900' }}">
                        <span class="size-2 rounded-full {{ $office['open'] ? 'bg-white' : 'bg-slate-400' }}" aria-hidden="true"></span>
                        {{ $office['open'] ? __('Open') : __('Closed') }}
                    </span>
                </div>
                <ol class="grid grid-cols-7 border-t border-slate-100 dark:border-slate-800">
                    @foreach ($office['days'] as $day)
                        <li class="px-1 py-3 text-center {{ $day['today'] ? 'bg-brand-50 dark:bg-brand-950/40' : '' }}">
                            <p class="text-[0.65rem] font-bold uppercase tracking-wider {{ $day['today'] ? 'text-brand-800 dark:text-brand-200' : 'text-slate-500' }}">{{ __($day['label']) }}</p>
                            <p class="mt-1 text-xs font-semibold {{ $day['shut'] ? 'text-slate-400' : 'text-slate-800 dark:text-slate-100' }}">{{ __($day['hours']) }}</p>
                        </li>
                    @endforeach
                </ol>
                <p class="border-t border-slate-100 px-5 py-3 text-xs leading-5 text-slate-500 dark:border-slate-800 dark:text-slate-400">{{ __('Monday to Friday, 8:00 am to 5:00 pm. The office is closed on Saturday, Sunday, and public holidays.') }}</p>
            </section>

            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-900">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Email') }}</dt>
                    <dd class="mt-1 text-sm font-medium"><a class="link" href="mailto:{{ config('public.contact.email') }}">{{ config('public.contact.email') }}</a></dd>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-900">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Telephone') }}</dt>
                    <dd class="mt-1 text-sm font-medium"><a class="link" href="tel:{{ config('public.contact.phone_href') }}">{{ config('public.contact.phone') }}</a></dd>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-900">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Office') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">{{ config('public.contact.office') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-900">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Response time') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">{{ config('public.contact.response_time') }}</dd>
                </div>
            </dl>
        </div>

        <aside class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-card sm:p-8 dark:border-slate-800 dark:bg-slate-900 lg:col-span-3" id="contact-form">
            @if (session('contact_reference'))
                <x-ui.alert type="success" class="mb-6" :title="__('Enquiry received')">
                    {{ __('Your reference is :reference. We have sent you a confirmation and our team will be in touch soon.', ['reference' => session('contact_reference')]) }}
                </x-ui.alert>
            @endif

            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Send a message') }}</p>
            <h2 class="font-display mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ __('How can we help?') }}</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('All fields marked * are required.') }}</p>

            <form action="{{ route('contact.store') }}" method="post" class="mt-6 space-y-5" novalidate>
                @csrf

                <div>
                    <x-ui.label for="name" required>{{ __('Full name') }}</x-ui.label>
                    <x-ui.input name="name" icon="user" :value="old('name', auth()->user()?->name)" required maxlength="255" autocomplete="name" placeholder="Ama Mensah" />
                    <x-ui.input-error for="name" />
                </div>

                <div>
                    <x-ui.label for="email" required>{{ __('Email') }}</x-ui.label>
                    <x-ui.input name="email" type="email" icon="mail" :value="old('email', auth()->user()?->email)" required maxlength="255" autocomplete="email" inputmode="email" placeholder="john.doe@business.com" />
                    <x-ui.input-error for="email" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-ui.label for="phone_number" required>{{ __('Phone') }}</x-ui.label>
                        <x-ui.input name="phone_number" icon="phone" :value="old('phone_number')" required maxlength="20" autocomplete="tel" inputmode="tel" placeholder="0241234567" />
                        <x-ui.input-error for="phone_number" />
                    </div>
                    <div>
                        <x-ui.label for="available_time_for_call">{{ __('Best time to call') }}</x-ui.label>
                        <select id="available_time_for_call" name="available_time_for_call" class="form-control mt-1.5">
                            <option value="">{{ __('Select a time (Africa/Accra GMT)') }}</option>
                            @foreach ($callWindows as $value => $label)
                                <option value="{{ $value }}" @selected(old('available_time_for_call') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-ui.input-error for="available_time_for_call" />
                    </div>
                </div>

                <div>
                    <x-ui.label for="subject" required>{{ __('Subject') }}</x-ui.label>
                    <x-ui.input name="subject" :value="old('subject')" required maxlength="255" />
                    <x-ui.input-error for="subject" />
                </div>

                <div>
                    <x-ui.label for="message" required>{{ __('Message') }}</x-ui.label>
                    <textarea id="message" name="message" rows="6" required minlength="20" maxlength="5000" class="form-control mt-1.5" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                    <x-ui.input-error for="message" />
                </div>

                <div>
                    <div class="flex items-start gap-3">
                        <input id="consent" name="consent" type="checkbox" value="1" required class="form-check mt-0.5" @checked(old('consent')) @error('consent') aria-invalid="true" aria-describedby="consent-error" @enderror>
                        <label for="consent" class="text-sm text-slate-700 dark:text-slate-200">{{ __('I consent to GIPA using these details to respond to my enquiry.') }}</label>
                    </div>
                    <x-ui.input-error for="consent" />
                </div>

                <div class="honeypot" aria-hidden="true">
                    <label for="website">{{ __('Website') }}</label>
                    <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <x-ui.button variant="gold" size="lg">
                    <i data-lucide="send" class="size-4"></i>
                    {{ __('Send enquiry') }}
                </x-ui.button>

                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('We use your details only to respond to this enquiry.') }}</p>
            </form>
        </aside>
    </div>
</x-layouts.public>
