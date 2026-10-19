<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Application\NotifyUser;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;

/**
 * Compares a provider balance with the thresholds an administrator set.
 * Frog spends SMS bundle units first and falls back to cash credit.
 * A credit-only gateway, such as Twilio, alerts on the cash threshold alone.
 */
final class BalanceWatch
{
    public function __construct(private readonly NotifyUser $notifications) {}

    public function level(SmsProviderConfig $config, ProviderBalance $balance): ?string
    {
        if (! $balance->available) {
            return null;
        }

        $bundles = (bool) ($config->definition()['supports_bundles'] ?? false);
        $bundleLow = $bundles
            && $config->bundle_threshold !== null
            && ($balance->smsUnits ?? 0) <= $config->bundle_threshold;
        $creditLow = $config->credit_threshold !== null
            && ($balance->creditBalance ?? 0) <= (float) $config->credit_threshold;

        if ($bundles && $bundleLow && ! $creditLow) {
            return 'credit_fallback';
        }

        if ($creditLow && (! $bundles || $bundleLow)) {
            return 'top_up';
        }

        return null;
    }

    public function notifyIfNeeded(SmsProviderConfig $config): void
    {
        $snapshot = $config->balance_snapshot;

        if (! is_array($snapshot)) {
            return;
        }

        $level = $this->level($config, ProviderBalance::fromArray($config->provider, $snapshot));

        if ($level === null) {
            $config->forceFill([
                'balance_alert_level' => null,
                'balance_alerted_at' => null,
            ])->save();

            return;
        }

        if ($config->balance_alert_level === $level && $config->balance_alerted_at?->gt(now()->subDay())) {
            return;
        }

        $name = $config->displayName();
        [$title, $body] = $level === 'credit_fallback'
            ? [
                $name.' bundles are low',
                $name.' SMS bundle units are at or below the threshold. Cash credit can still send until it is topped up.',
            ]
            : [
                $name.' needs a top-up',
                $name.' has reached the balance threshold. Bundle units and cash credit are both too low to keep sending.',
            ];

        $this->notifications->sendToPermission('sms.manage', $title, $body, route('admin.sms.index'));
        $config->forceFill([
            'balance_alert_level' => $level,
            'balance_alerted_at' => now(),
        ])->save();
    }
}
