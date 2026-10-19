@php
    $profile = $user->investorProfile;
    $approved = $profile?->canEngage() === true;
@endphp

<x-layouts.workspace :title="__('Dashboard')">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.8fr)] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Investor overview') }}</p>
                <h2 class="font-display mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ __('Welcome, :name', ['name' => $user->name]) }}</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    @if ($profile)
                        {{ $profile->organization_name }}
                        · {{ $profile->onboarding_state->label() }}
                    @else
                        {{ __('Save a business profile to keep this workspace open.') }}
                    @endif
                </p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('investor.opportunities') }}" class="cta bg-brand-700 text-white hover:bg-brand-800">
                        <i data-lucide="map-pinned" class="size-4"></i>
                        {{ __('Review opportunities') }}
                    </a>
                    <a href="{{ route('investor.map') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">
                        <i data-lucide="map" class="size-4"></i>
                        {{ __('Investment map') }}
                    </a>
                </div>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/40">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-200">{{ __('Verification') }}</p>
                <p class="mt-3 flex items-center gap-2 text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                    <i data-lucide="badge-check" class="size-4"></i>
                    {{ __('E-mail and phone verified') }}
                </p>
                <p class="mt-3 text-sm leading-6 text-emerald-900/80 dark:text-emerald-100/80">
                    @if ($approved)
                        {{ __('This profile is approved. Inquiry and interest are open on active records.') }}
                    @else
                        {{ __('You can browse active records. Inquiry and interest open after GIPA approves the profile.') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Active opportunities') }}</p>
                <i data-lucide="map-pinned" class="size-4 text-brand-700 dark:text-brand-300"></i>
            </div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($overview['active']) }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Approved and active across Ghana.') }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Inquiries') }}</p>
                <i data-lucide="mail" class="size-4 text-brand-700 dark:text-brand-300"></i>
            </div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($overview['inquiries']) }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Sent from this account.') }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Interest') }}</p>
                <i data-lucide="handshake" class="size-4 text-brand-700 dark:text-brand-300"></i>
            </div>
            <p class="font-display mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($overview['interests']) }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Records you have marked.') }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Business profile') }}</p>
                <i data-lucide="{{ $approved ? 'badge-check' : 'hourglass' }}" class="size-4 text-brand-700 dark:text-brand-300"></i>
            </div>
            <p class="font-display mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ $profile?->onboarding_state->label() ?? __('Not saved') }}</p>
            <a href="{{ route('investor.profile') }}" class="link mt-2 inline-flex text-sm">{{ __('Open profile') }}</a>
        </article>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-5">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900 lg:col-span-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold text-slate-900 dark:text-white">{{ __('Recent engagement') }}</h2>
                <a href="{{ route('investor.engagement') }}" class="link text-sm">{{ __('View all') }}</a>
            </div>
            @if ($overview['recent']->isEmpty())
                <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Inquiries and interest you submit will be listed here with their reference.') }}</p>
            @else
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($overview['recent'] as $engagement)
                        <li class="flex items-start justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $engagement->opportunity?->title ?? __('Opportunity') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $engagement->reference }} · {{ $engagement->kind->label() }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-500">{{ $engagement->submitted_at?->timezone('Africa/Accra')->format('d M Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900 dark:text-white">{{ __('Visible pipeline') }}</h2>
                <i data-lucide="banknote" class="size-4 text-brand-700 dark:text-brand-300"></i>
            </div>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Active records only. Amounts stay in their own currency.') }}</p>
            @if ($overview['pipeline']->isEmpty())
                <p class="mt-4 text-sm text-slate-500">{{ __('No active records yet.') }}</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($overview['pipeline'] as $row)
                        <li class="flex items-end justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-slate-950">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $row->currency }}</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ number_format((int) $row->total) }} {{ trans_choice('record|records', (int) $row->total) }}</p>
                            </div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ number_format((float) $row->amount, 0) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.workspace>
