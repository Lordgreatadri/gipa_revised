<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks\SignatureValidators;

use App\Modules\Shared\Infrastructure\Sms\Webhooks\SecureCompare;
use App\Modules\Shared\Infrastructure\Sms\Webhooks\WebhookSecretResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

/**
 * Frog does not publish a signature scheme. The accepted URL carries a shared
 * token, and an HMAC-SHA256 header is also accepted when Frog sends one.
 */
class FrogSmsSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $secret = WebhookSecretResolver::secret('frog');

        if ($secret === null) {
            Log::channel('sms-webhooks')->warning('Frog webhook rejected: no signing secret is configured.');

            return false;
        }

        $signature = trim((string) $request->header($config->signatureHeaderName, ''));

        if ($signature !== '') {
            $body = $request->getContent();
            $hex = hash_hmac('sha256', $body, $secret);
            $base64 = base64_encode(hash_hmac('sha256', $body, $secret, true));

            return SecureCompare::equals($hex, $signature) || SecureCompare::equals($base64, $signature);
        }

        $token = (string) ($request->header('X-Webhook-Token') ?: $request->query('token', ''));

        if ($token === '') {
            Log::channel('sms-webhooks')->warning('Frog webhook rejected: no signature or token supplied.');

            return false;
        }

        return SecureCompare::equals($secret, $token);
    }
}
