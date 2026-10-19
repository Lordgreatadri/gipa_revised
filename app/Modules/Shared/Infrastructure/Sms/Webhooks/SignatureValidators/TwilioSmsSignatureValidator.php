<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks\SignatureValidators;

use App\Modules\Shared\Infrastructure\Sms\Webhooks\SecureCompare;
use App\Modules\Shared\Infrastructure\Sms\Webhooks\WebhookSecretResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

/**
 * Twilio signs the full callback URL plus every POST field, sorted by key,
 * with HMAC-SHA1 keyed by the account auth token.
 *
 * @see https://www.twilio.com/docs/usage/security#validating-requests
 */
class TwilioSmsSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $token = WebhookSecretResolver::credential('twilio', 'auth_token');
        $signature = trim((string) $request->header($config->signatureHeaderName, ''));

        if ($token === null || $signature === '') {
            Log::channel('sms-webhooks')->warning('Twilio webhook rejected: missing auth token or signature header.');

            return false;
        }

        $payload = $request->post();
        ksort($payload);

        $data = $request->fullUrl();

        foreach ($payload as $key => $value) {
            $data .= $key.(is_scalar($value) ? (string) $value : json_encode($value));
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $token, true));

        return SecureCompare::equals($expected, $signature);
    }
}
