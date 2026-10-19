<x-layouts.workspace :title="$opportunity->title">
    <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 text-white shadow-card">
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-100">{{ $opportunity->reference }}</p>
                <h2 class="mt-3 text-3xl font-bold">{{ $opportunity->title }}</h2>
                <p class="mt-3 inline-flex items-center gap-2 text-sm text-brand-50"><i data-lucide="map-pin" class="size-4"></i>{{ $opportunity->district?->name }}, {{ $opportunity->district?->region?->name }}</p>
                <p class="mt-4 text-sm text-brand-100">{{ $opportunity->sector?->name }}@if ($opportunity->subSector) · {{ $opportunity->subSector->name }}@endif</p>
            </div>
            <div class="rounded-2xl bg-white/10 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-brand-100">{{ __('Investment required') }}</p>
                <p class="mt-2 text-2xl font-bold">{{ $opportunity->currency }} {{ number_format((float) $opportunity->investment_amount, 0) }}</p>
                <p class="mt-2 text-sm text-brand-50">{{ $opportunity->enterpriseType?->name }}</p>
                <a href="#inquiry" class="cta mt-4 bg-gold-500 text-brand-950 hover:bg-gold-400">{{ __('Express interest') }}</a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid gap-6">
            <x-ui.card :title="__('Overview')" heading="h2">
                <p class="text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $opportunity->description }}</p>
                @if ($opportunity->location_description)
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-300"><strong>{{ __('Location context') }}</strong> {{ $opportunity->location_description }}</p>
                @endif
                @if ($opportunity->objectives)
                    <h3 class="mt-6 text-sm font-bold text-slate-900 dark:text-white">{{ __('Objectives') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $opportunity->objectives }}</p>
                @endif
                @if ($opportunity->competitive_advantages)
                    <h3 class="mt-6 text-sm font-bold text-slate-900 dark:text-white">{{ __('Competitive advantages') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $opportunity->competitive_advantages }}</p>
                @endif
            </x-ui.card>
            <x-ui.card :title="__('Commercial profile')" heading="h2">
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div><dt class="text-slate-500">{{ __('Structure') }}</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->investment_structure->label() }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Project status') }}</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->project_status->label() }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Expected ROI') }}</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->roi !== null ? number_format((float) $opportunity->roi, 2).'%' : __('On request') }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Payback') }}</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->payback_period_months ? $opportunity->payback_period_months.' '.__('months') : __('On request') }}</dd></div>
                </dl>
            </x-ui.card>
        </div>

        <aside id="inquiry" class="grid content-start gap-4">
            <x-ui.card :title="__('Inquiry')" :description="__('Ask the investment desk for more on this opportunity.')" heading="h2">
                <form method="POST" action="{{ route('investor.opportunities.inquire', $opportunity) }}" class="grid gap-3">
                    @csrf
                    <input class="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div>
                        <x-ui.label for="message" required>{{ __('Message') }}</x-ui.label>
                        <textarea id="message" name="message" rows="5" required minlength="20" class="form-control">{{ old('message') }}</textarea>
                        <x-ui.input-error for="message" />
                    </div>
                    <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="consent" value="1" class="mt-1" @checked(old('consent')) required>
                        <span>{{ __('I consent to GIPA using my account details to respond to this inquiry.') }}</span>
                    </label>
                    <x-ui.input-error for="consent" />
                    <x-ui.button>{{ __('Submit inquiry') }}</x-ui.button>
                </form>
            </x-ui.card>
            <x-ui.card :title="__('Express interest')" :description="__('Tell GIPA you want to be considered for matchmaking on this opportunity.')" heading="h2">
                <form method="POST" action="{{ route('investor.opportunities.interest', $opportunity) }}" class="grid gap-3">
                    @csrf
                    <input class="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="consent" value="1" class="mt-1" required>
                        <span>{{ __('I consent to GIPA recording my interest against this opportunity.') }}</span>
                    </label>
                    <x-ui.button variant="secondary">{{ __('Record interest') }}</x-ui.button>
                </form>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.workspace>
