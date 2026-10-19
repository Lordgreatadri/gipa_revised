<x-layouts.workspace :title="__('SMS gateways')">
    <div class="mb-6 max-w-3xl">
        <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Frog and Twilio are the transmitting gateways. Log only records the message and does not call a network. Traffic uses the active gateway with the lowest priority. If that gateway cannot be reached, the next active gateway is tried. Switching traffic turns the others off.') }}</p>
    </div>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6">{{ $errors->first() }}</x-ui.alert>
    @endif

    @if ($active === [])
        <p class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-50">{{ __('No gateway is active, so outbound SMS is not sent.') }}</p>
    @else
        <p class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-50">
            {{ __('Transmission order:') }}
            @foreach ($active as $index => $config)
                <span class="font-semibold">{{ $index + 1 }}. {{ $config->displayName() }}</span>@if (! $loop->last)<span aria-hidden="true"> · </span>@endif
            @endforeach
        </p>
    @endif

    <div class="mb-6 flex flex-wrap gap-3">
        @can(\App\Modules\Identity\Domain\Access\AccessPermissions::SMS_MESSAGES_VIEW)
            <a href="{{ route('admin.sms.messages') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Transmission log') }}</a>
        @endcan
        @can(\App\Modules\Identity\Domain\Access\AccessPermissions::SMS_CAMPAIGNS_MANAGE)
            <a href="{{ route('admin.sms.campaigns') }}" class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800">{{ __('Bulk SMS') }}</a>
        @endcan
    </div>

    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-amber-500 p-5 text-amber-950 shadow-card"><p class="text-xs font-bold uppercase tracking-wider">{{ __('Queued') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($counts['queued']) }}</p></div>
        <div class="rounded-2xl bg-emerald-600 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-emerald-100">{{ __('Sent') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($counts['sent']) }}</p></div>
        <div class="rounded-2xl bg-sky-700 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-sky-100">{{ __('Delivered') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($counts['delivered']) }}</p></div>
        <div class="rounded-2xl bg-rose-600 p-5 text-white shadow-card"><p class="text-xs font-bold uppercase tracking-wider text-rose-100">{{ __('Failed') }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($counts['failed']) }}</p></div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        @foreach ($providers as $entry)
            @php($config = $entry['config'])
            @php($definition = $entry['definition'])
            <section class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $definition['label'] }}</h2>
                        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $entry['key'] }}</p>
                    </div>
                    @if ($config?->isUsable())
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-900 dark:bg-emerald-900 dark:text-emerald-50">{{ __('Active') }}</span>
                    @elseif ($config?->isConfigured())
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ __('Configured') }}</span>
                    @else
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-950 dark:bg-amber-900 dark:text-amber-50">{{ __('Not configured') }}</span>
                    @endif
                </div>

                <dl class="mt-4 grid gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <div class="flex justify-between gap-3"><dt>{{ __('Priority') }}</dt><dd class="font-semibold">{{ $config?->priority ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt>{{ __('Sender') }}</dt><dd class="font-semibold">{{ $config?->sender_id ?: '—' }}</dd></div>
                    @if ($config?->missingCredentials())
                        <div><dt class="text-rose-700 dark:text-rose-300">{{ __('Still needed') }}</dt><dd>{{ implode(', ', $config->missingCredentials()) }}</dd></div>
                    @endif
                    @if ($config?->last_failure_reason)
                        <div><dt class="text-rose-700 dark:text-rose-300">{{ __('Last failure') }}</dt><dd>{{ $config->last_failure_reason }}</dd></div>
                    @endif
                    @if ($config?->balance_snapshot)
                        <div class="flex justify-between gap-3">
                            <dt>{{ __('Balance') }}</dt>
                            <dd class="font-semibold">
                                @if ($config->balance_snapshot['available'] ?? false)
                                    {{ $config->balance_snapshot['currency'] ?? '' }} {{ number_format((float) ($config->balance_snapshot['credit_balance'] ?? 0), 2) }}
                                    @if (isset($config->balance_snapshot['sms_units']))
                                        · {{ number_format((int) $config->balance_snapshot['sms_units']) }} {{ __('SMS units') }}
                                    @endif
                                @else
                                    {{ $config->balance_snapshot['message'] ?? __('Unavailable') }}
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if ($entry['alert'] === 'credit_fallback')
                        <div><dt class="text-amber-800 dark:text-amber-200">{{ __('Bundles low') }}</dt><dd>{{ __('Cash credit can still send. Top up the bundle.') }}</dd></div>
                    @elseif ($entry['alert'] === 'top_up')
                        <div><dt class="text-rose-700 dark:text-rose-300">{{ __('Top-up needed') }}</dt><dd>{{ __('Bundles and credit are both at or below the thresholds.') }}</dd></div>
                    @endif
                </dl>

                @if ($config?->hasStoredSecret())
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ __('A secret is stored. It is not shown on this page.') }}</p>
                @endif

                @if ($config?->supportsCallbacks() && $config->acceptedCallbackUrl())
                    <div class="mt-4">
                        <x-ui.label for="callback-{{ $entry['key'] }}">{{ __('Accepted callback URL') }}</x-ui.label>
                        <input id="callback-{{ $entry['key'] }}" class="form-control font-mono text-xs" readonly value="{{ $config->acceptedCallbackUrl() }}">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            @if ($entry['key'] === 'frog')
                                {{ __('Copy this URL into the Frog console. The token in it is the only proof a delivery report is genuine. Treat the URL as a secret.') }}
                            @else
                                {{ __('Copy this URL into the Twilio console as the status callback. Twilio signs each report with the auth token.') }}
                            @endif
                        </p>
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('admin.sms.edit', $entry['key']) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ $config ? __('Edit') : __('Configure') }}</a>
                    @if ($config?->isConfigured())
                        <form method="POST" action="{{ route('admin.sms.switch', $entry['key']) }}">
                            @csrf
                            <button class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Switch traffic here') }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.sms.toggle', $entry['key']) }}">
                            @csrf
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800" type="submit">{{ $config->is_active ? __('Deactivate') : __('Activate as failover') }}</button>
                        </form>
                    @endif
                    @if ($config?->supportsBalance() && $config->isConfigured())
                        <form method="POST" action="{{ route('admin.sms.balance', $entry['key']) }}">
                            @csrf
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800" type="submit">{{ __('Refresh balance') }}</button>
                        </form>
                    @endif
                </div>

                @if ($config?->isUsable())
                    <form method="POST" action="{{ route('admin.sms.test', $entry['key']) }}" class="mt-4 grid gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                        @csrf
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Send a test through this gateway only') }}</p>
                        <div>
                            <x-ui.label for="recipient-{{ $entry['key'] }}">{{ __('Recipient') }}</x-ui.label>
                            <x-ui.input id="recipient-{{ $entry['key'] }}" name="recipient" maxlength="20" placeholder="+233…" required />
                        </div>
                        <div>
                            <x-ui.label for="body-{{ $entry['key'] }}">{{ __('Message') }}</x-ui.label>
                            <textarea id="body-{{ $entry['key'] }}" name="body" class="form-control" rows="3" minlength="10" maxlength="320" required>{{ __('IOMP SMS configuration test.') }}</textarea>
                        </div>
                        <x-ui.button>{{ __('Send test') }}</x-ui.button>
                    </form>
                @endif
            </section>
        @endforeach
    </div>
</x-layouts.workspace>
