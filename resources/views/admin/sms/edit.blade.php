<x-layouts.workspace :title="$definition['label'].' configuration'">
    <p class="mb-6 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
        <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('admin.sms.index') }}">{{ __('SMS gateways') }}</a>
        <span aria-hidden="true"> / </span>
        {{ $definition['label'] }}
    </p>

    <form method="POST" action="{{ route('admin.sms.update', $provider) }}" class="grid max-w-3xl gap-6">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Routing') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('A lower priority number is tried first when more than one gateway is active.') }}</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label for="label">{{ __('Display label') }}</x-ui.label>
                    <x-ui.input id="label" name="label" maxlength="120" :value="old('label', $config?->label)" :placeholder="$definition['label']" />
                </div>
                <div>
                    <x-ui.label for="sender_id">{{ __('Sender ID') }}</x-ui.label>
                    <x-ui.input id="sender_id" name="sender_id" maxlength="32" :value="old('sender_id', $config?->sender_id)" placeholder="GIPA" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('An approved alphanumeric sender, or the Twilio number that sends the message.') }}</p>
                </div>
                <div>
                    <x-ui.label for="priority" required>{{ __('Failover priority') }}</x-ui.label>
                    <x-ui.input id="priority" name="priority" type="number" min="1" max="999" required :value="old('priority', $config?->priority ?? 100)" />
                </div>
                <div>
                    <x-ui.label for="is_active">{{ __('Status') }}</x-ui.label>
                    <select id="is_active" name="is_active" class="form-control">
                        <option value="0" @selected(old('is_active', $config?->is_active ? '1' : '0') === '0')>{{ __('Inactive') }}</option>
                        <option value="1" @selected(old('is_active', $config?->is_active ? '1' : '0') === '1')>{{ __('Active') }}</option>
                    </select>
                </div>
            </div>
        </section>

        @if ($definition['credentials'])
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('API credentials') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Values are encrypted before they are stored. Leave a secret blank to keep the stored value.') }}
                    @if ($definition['docs_url'])
                        <a class="font-semibold underline" href="{{ $definition['docs_url'] }}" rel="noopener noreferrer" target="_blank">{{ __('Provider documentation') }}</a>
                    @endif
                </p>
                <div class="mt-4 grid gap-4">
                    @foreach ($definition['credentials'] as $key => $meta)
                        @php
                            $stored = $config?->credential($key);
                        @endphp
                        <div>
                            <x-ui.label for="credential-{{ $key }}">
                                {{ $meta['label'] }}
                                @if ($meta['required'] ?? false)<span class="text-rose-600">*</span>@endif
                            </x-ui.label>
                            <input
                                id="credential-{{ $key }}"
                                class="form-control"
                                type="{{ ($meta['secret'] ?? false) ? 'password' : 'text' }}"
                                name="credentials[{{ $key }}]"
                                value="{{ ($meta['secret'] ?? false) ? '' : old('credentials.'.$key, $stored) }}"
                                maxlength="500"
                                autocomplete="off"
                                @if(($meta['secret'] ?? false) && filled($stored)) placeholder="{{ __('Stored — leave blank to keep') }}" @endif
                            >
                            @if ($meta['hint'] ?? null)
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $meta['hint'] }}</p>
                            @endif
                            @if ($provider === 'frog' && $key === 'base_url')
                                @php
                                    $resolvedBase = old('credentials.base_url', $stored) ?: ($definition['defaults']['base_url'] ?? '');
                                    $resolvedSend = $resolvedBase !== ''
                                        ? \App\Modules\Shared\Infrastructure\Sms\Providers\FrogEndpoint::url((string) $resolvedBase, 'sms/send')
                                        : '';
                                @endphp
                                @if ($resolvedSend !== '')
                                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ __('Send requests are posted to') }} <span class="font-mono">{{ $resolvedSend }}</span>. {{ __('Frog documents https://frogapi.wigal.com.gh/api/v3/sms/send.') }}</p>
                                @endif
                            @endif
                            @error('credentials.'.$key)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($definition['supports_balance'] ?? false)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Balance thresholds') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('When a refresh finds the balance at or below these figures, the messaging team gets an in-app alert. Leave a field blank to ignore it. Frog spends bundle units first and uses cash credit after the bundle is finished.') }}</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label for="credit_threshold">{{ __('Cash credit threshold') }}</x-ui.label>
                        <x-ui.input id="credit_threshold" name="credit_threshold" type="number" min="0" step="0.01" :value="old('credit_threshold', $config?->credit_threshold)" />
                    </div>
                    @if ($definition['supports_bundles'] ?? false)
                        <div>
                            <x-ui.label for="bundle_threshold">{{ __('SMS bundle units threshold') }}</x-ui.label>
                            <x-ui.input id="bundle_threshold" name="bundle_threshold" type="number" min="0" step="1" :value="old('bundle_threshold', $config?->bundle_threshold)" />
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($definition['supports_delivery_callbacks'] ?? false)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Delivery-report callback') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Copy the accepted URL into the provider console. A callback is stored only after its signature or shared token checks pass.') }}</p>
                <div class="mt-4 grid gap-4">
                    <div>
                        <x-ui.label for="callback-url">{{ __('Accepted callback URL') }}</x-ui.label>
                        @if ($config?->acceptedCallbackUrl())
                            <input id="callback-url" class="form-control font-mono text-xs" readonly value="{{ $config->acceptedCallbackUrl() }}">
                        @else
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Save this configuration to generate the callback URL.') }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            @if ($provider === 'frog')
                                {{ __('Frog does not sign callbacks, so the shared token in the query string authenticates the request. Rotating it invalidates the URL already stored in the Frog console.') }}
                            @else
                                {{ __('Twilio signs callbacks with the auth token, so this URL does not carry an extra secret. The signature covers the exact URL and every posted field.') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <x-ui.label for="rotate_webhook_secret">{{ __('Callback secret') }}</x-ui.label>
                        <select id="rotate_webhook_secret" name="rotate_webhook_secret" class="form-control">
                            <option value="0">{{ __('Keep current secret') }}</option>
                            <option value="1">{{ __('Generate a new secret') }}</option>
                        </select>
                    </div>
                </div>
            </section>
        @endif

        <div class="flex flex-wrap gap-3">
            <x-ui.button class="w-auto">{{ __('Save configuration') }}</x-ui.button>
            <a href="{{ route('admin.sms.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-layouts.workspace>
