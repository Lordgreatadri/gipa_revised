<?php

namespace App\Modules\Shared\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Shared\Infrastructure\Sms\BalanceWatch;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\SmsGateway;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SmsSettingsController
{
    public function index(SmsProviderRegistry $registry, BalanceWatch $watch): View
    {
        $counts = SmsMessage::query()
            ->select('status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $providers = [];

        foreach ($registry->catalogue() as $key => $definition) {
            $config = $registry->configFor($key);
            $alert = null;

            if ($config !== null && is_array($config->balance_snapshot)) {
                $alert = $watch->level($config, ProviderBalance::fromArray($key, $config->balance_snapshot));
            }

            $providers[] = [
                'key' => $key,
                'definition' => $definition,
                'config' => $config,
                'alert' => $alert,
            ];
        }

        return view('admin.sms.index', [
            'providers' => $providers,
            'active' => $registry->usable(),
            'counts' => [
                'queued' => (int) ($counts[SmsMessage::QUEUED] ?? 0),
                'sent' => (int) ($counts[SmsMessage::SENT] ?? 0),
                'delivered' => (int) ($counts[SmsMessage::DELIVERED] ?? 0),
                'failed' => (int) ($counts[SmsMessage::FAILED] ?? 0) + (int) ($counts[SmsMessage::UNDELIVERED] ?? 0),
            ],
        ]);
    }

    public function edit(string $provider, SmsProviderRegistry $registry): View
    {
        abort_unless($registry->supports($provider), 404);

        return view('admin.sms.edit', [
            'provider' => $provider,
            'definition' => $registry->definition($provider),
            'config' => $registry->configFor($provider),
        ]);
    }

    public function update(Request $request, string $provider, SmsProviderRegistry $registry, AuditRecorder $audit): RedirectResponse
    {
        abort_unless($registry->supports($provider), 404);

        $definition = $registry->definition($provider);
        $config = $this->record($provider, $request);
        $rules = [
            'label' => ['nullable', 'string', 'max:120'],
            'sender_id' => ['nullable', 'string', 'max:32'],
            'priority' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'rotate_webhook_secret' => ['nullable', 'boolean'],
            'credit_threshold' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'bundle_threshold' => ['nullable', 'integer', 'min:0', 'max:10000000'],
        ];

        foreach ($definition['credentials'] ?? [] as $key => $meta) {
            $rules['credentials.'.$key] = ['nullable', 'string', 'max:500'];
        }

        $data = $request->validate($rules);
        $credentials = $this->mergeCredentials($config, $definition, (array) $request->input('credentials', []));
        $rotated = false;
        $secret = $config->webhook_secret;

        if (($definition['supports_delivery_callbacks'] ?? false) && (blank($secret) || $request->boolean('rotate_webhook_secret'))) {
            $secret = Str::random(48);
            $rotated = filled($config->webhook_secret);
        }

        $config->forceFill([
            'label' => ($data['label'] ?? null) ?: null,
            'sender_id' => ($data['sender_id'] ?? null) ?: null,
            'priority' => (int) $data['priority'],
            'credentials' => $credentials,
            'webhook_secret' => $secret,
            'credit_threshold' => ($definition['supports_balance'] ?? false) ? ($data['credit_threshold'] ?? null) : null,
            'bundle_threshold' => ($definition['supports_bundles'] ?? false) ? ($data['bundle_threshold'] ?? null) : null,
            'updated_by' => $request->user()->id,
        ]);
        $config->save();
        $config->refresh();

        $wantsActive = $request->boolean('is_active');

        if ($wantsActive && ! $config->isConfigured()) {
            $config->forceFill(['is_active' => false])->save();

            return redirect()
                ->route('admin.sms.edit', $provider)
                ->withErrors(['is_active' => 'Add every required credential before this gateway can carry traffic.']);
        }

        $config->forceFill(['is_active' => $wantsActive && $config->isConfigured()])->save();
        $registry->flush();

        $audit->record(
            AuditEntry::for('sms.settings_updated', $config)
                ->causedBy($request->user())
                ->withMetadata([
                    'provider' => $provider,
                    'is_active' => $config->is_active,
                    'priority' => $config->priority,
                    'sender_id' => $config->sender_id,
                    'configured' => $config->isConfigured(),
                    'callback_secret_rotated' => $rotated,
                ]),
        );

        return redirect()
            ->route('admin.sms.index')
            ->with('status', $config->displayName().' saved. Secret fields stay encrypted and are not shown again.');
    }

    public function switch(Request $request, string $provider, SmsProviderRegistry $registry, AuditRecorder $audit): RedirectResponse
    {
        abort_unless($registry->supports($provider), 404);

        $config = $registry->configFor($provider);

        if ($config === null || ! $config->isConfigured()) {
            return redirect()
                ->route('admin.sms.edit', $provider)
                ->withErrors(['sms' => 'Save the required credentials before switching traffic to this gateway.']);
        }

        SmsProviderConfig::query()->whereKeyNot($config->id)->update(['is_active' => false]);
        $config->forceFill([
            'is_active' => true,
            'priority' => 10,
            'updated_by' => $request->user()->id,
        ])->save();
        $registry->flush();

        $audit->record(
            AuditEntry::for('sms.provider_switched', $config)
                ->causedBy($request->user())
                ->withMetadata(['provider' => $provider]),
        );

        return back()->with('status', $config->displayName().' now carries SMS traffic. The other gateways are inactive.');
    }

    public function toggle(Request $request, string $provider, SmsProviderRegistry $registry, AuditRecorder $audit): RedirectResponse
    {
        abort_unless($registry->supports($provider), 404);

        $config = $registry->configFor($provider);

        if ($config === null) {
            return redirect()->route('admin.sms.edit', $provider);
        }

        $next = ! $config->is_active;

        if ($next && ! $config->isConfigured()) {
            return back()->withErrors(['sms' => 'This gateway still needs its required credentials.']);
        }

        $config->forceFill([
            'is_active' => $next,
            'updated_by' => $request->user()->id,
        ])->save();

        $audit->record(
            AuditEntry::for('sms.provider_toggled', $config)
                ->causedBy($request->user())
                ->withMetadata(['provider' => $provider, 'is_active' => $next]),
        );

        return back()->with('status', $next
            ? $config->displayName().' is active and will be used in priority order.'
            : $config->displayName().' is inactive.');
    }

    public function balance(string $provider, SmsProviderRegistry $registry, BalanceWatch $watch): RedirectResponse
    {
        abort_unless($registry->supports($provider), 404);

        $config = $registry->configFor($provider);

        if ($config === null || ! $config->supportsBalance() || ! $config->isConfigured()) {
            return back()->withErrors(['sms' => 'Balance is available after this gateway is configured.']);
        }

        $balance = $registry->makeFor($config)->balance();
        $config->forceFill([
            'balance_snapshot' => $balance->toArray(),
            'balance_checked_at' => now(),
        ])->save();
        $watch->notifyIfNeeded($config->fresh());

        return back()->with('status', $balance->available
            ? 'Balance refreshed.'
            : ($balance->message ?: 'The provider did not return a balance.'));
    }

    public function test(Request $request, string $provider, SmsProviderRegistry $registry, SmsGateway $gateway, AuditRecorder $audit): RedirectResponse
    {
        abort_unless($registry->supports($provider), 404);

        $config = $registry->configFor($provider);

        if ($config === null || ! $config->isUsable()) {
            return back()->withErrors(['sms' => 'Activate a configured gateway before sending a test.']);
        }

        $data = $request->validate([
            'recipient' => ['required', 'string', 'max:20'],
            'body' => ['required', 'string', 'min:10', 'max:320'],
        ]);

        $message = $gateway->sendNow($data['recipient'], $data['body'], 'sms.test', $provider);

        $audit->record(
            AuditEntry::for('sms.test_queued', $message)
                ->causedBy($request->user())
                ->withMetadata([
                    'provider' => $provider,
                    'status' => $message?->status,
                ]),
        );

        if ($message === null) {
            return back()->withErrors(['recipient' => 'Enter a phone number that can receive SMS.']);
        }

        if ($message->status !== SmsMessage::SENT) {
            return back()->withErrors(['sms' => 'The test was not accepted. Check the gateway credentials and sender ID.']);
        }

        return back()->with('status', 'Test accepted by '.$config->displayName().'.');
    }

    private function record(string $provider, Request $request): SmsProviderConfig
    {
        $config = SmsProviderConfig::query()->where('provider', $provider)->first();

        if ($config !== null) {
            return $config;
        }

        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => $provider,
            'created_by' => $request->user()->id,
            'priority' => 100,
        ]);

        return $config;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $incoming
     * @return array<string, string>
     */
    private function mergeCredentials(SmsProviderConfig $config, array $definition, array $incoming): array
    {
        $merged = $config->credentials ?? [];

        foreach ($definition['credentials'] ?? [] as $key => $meta) {
            $value = trim((string) ($incoming[$key] ?? ''));

            if (($meta['secret'] ?? false) && $value === '') {
                continue;
            }

            if ($value === '') {
                unset($merged[$key]);

                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }
}
