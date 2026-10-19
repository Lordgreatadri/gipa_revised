<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks;

use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;

/**
 * Reads the callback secret from the encrypted provider row at request time.
 */
class WebhookSecretResolver
{
    public static function secret(string $provider): ?string
    {
        $secret = SmsProviderConfig::query()->where('provider', $provider)->first()?->webhook_secret;

        return blank($secret) ? null : (string) $secret;
    }

    public static function credential(string $provider, string $key): ?string
    {
        return SmsProviderConfig::query()->where('provider', $provider)->first()?->credential($key);
    }
}
