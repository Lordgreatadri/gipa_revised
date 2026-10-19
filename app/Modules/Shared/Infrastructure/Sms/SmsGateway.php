<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Infrastructure\Sms\Data\OutgoingSms;
use App\Modules\Shared\Infrastructure\Sms\Data\SmsResult;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Queues an outbound message, then walks the active providers in priority
 * order. A transport failure tries the next active provider. Nothing is sent
 * when every provider is inactive, so an unconfigured environment stays quiet.
 */
final class SmsGateway
{
    public function __construct(private readonly SmsProviderRegistry $registry) {}

    public function queue(?string $phone, string $body, string $context): ?SmsMessage
    {
        if ($this->registry->usable() === []) {
            return null;
        }

        $message = $this->persist($phone, $body, $context, null);

        if ($message === null) {
            return null;
        }

        SendSmsMessage::dispatch($message->id)->onQueue((string) config('sms.queue', 'default'));

        return $message;
    }

    /**
     * Delivers at once through the active gateways, in priority order.
     * Used for a phone verification code, which should not wait on a worker.
     */
    public function sendImmediate(?string $phone, string $body, string $context): ?SmsMessage
    {
        if ($this->registry->usable() === []) {
            return null;
        }

        $message = $this->persist($phone, $body, $context, null);

        if ($message === null) {
            return null;
        }

        $this->deliver($message);

        return $message->refresh();
    }

    public function sendNow(string $phone, string $body, string $context, string $provider): ?SmsMessage
    {
        $message = $this->persist($phone, $body, $context, $provider);

        if ($message === null) {
            return null;
        }

        $this->deliver($message, exclusive: true);

        return $message->refresh();
    }

    public function deliver(SmsMessage $message, bool $exclusive = false): void
    {
        $chain = $this->chain($message->preferred_provider, $exclusive);
        $attempts = [];

        foreach ($chain as $config) {
            $result = $this->transmit($message, $config);
            $attempts[] = [
                'provider' => $config->provider,
                'status' => $result->status,
                'ok' => $result->successful,
            ];

            if ($result->successful) {
                $config->forceFill([
                    'last_success_at' => now(),
                    'consecutive_failures' => 0,
                    'last_failure_reason' => null,
                ])->save();
                $message->forceFill([
                    'status' => SmsMessage::SENT,
                    'provider' => $config->provider,
                    'provider_message_id' => $result->providerMessageId,
                    'sent_at' => now(),
                    'error' => null,
                    'attempts' => $attempts,
                ])->save();

                return;
            }

            $config->forceFill([
                'last_failure_at' => now(),
                'last_failure_reason' => Str::limit($result->message, 180, ''),
                'consecutive_failures' => $config->consecutive_failures + 1,
            ])->save();
        }

        $message->forceFill([
            'status' => $chain === [] ? SmsMessage::SKIPPED : SmsMessage::FAILED,
            'error' => $chain === []
                ? 'No SMS provider is active.'
                : Str::limit((string) (end($attempts)['status'] ?? 'Delivery failed.'), 500, ''),
            'attempts' => $attempts,
        ])->save();
    }

    /**
     * @return list<SmsProviderConfig>
     */
    private function chain(?string $preferred, bool $exclusive): array
    {
        $usable = $this->registry->usable();

        if ($exclusive) {
            return array_values(array_filter(
                $usable,
                fn (SmsProviderConfig $config): bool => $config->provider === $preferred,
            ));
        }

        if ($preferred === null) {
            return $usable;
        }

        $first = [];
        $rest = [];

        foreach ($usable as $config) {
            if ($config->provider === $preferred) {
                $first[] = $config;
            } else {
                $rest[] = $config;
            }
        }

        return [...$first, ...$rest];
    }

    private function transmit(SmsMessage $message, SmsProviderConfig $config): SmsResult
    {
        try {
            return $this->registry->makeFor($config)->send(new OutgoingSms(
                reference: $message->reference,
                recipient: $this->format($message->recipient, $config->provider),
                body: $message->body,
                senderId: $config->sender_id ?: 'GIPA',
                callbackUrl: $config->callbackUrl(),
            ));
        } catch (\Throwable $e) {
            logger()->channel('sms')->warning('SMS provider call failed', [
                'reference' => $message->reference,
                'provider' => $config->provider,
                'exception' => $e->getMessage(),
            ]);

            return SmsResult::transportFailure('PROVIDER_UNAVAILABLE', 'The provider could not be reached.');
        }
    }

    private function persist(?string $phone, string $body, string $context, ?string $preferred): ?SmsMessage
    {
        $recipient = $this->digits($phone);

        if ($recipient === null || trim($body) === '') {
            return null;
        }

        $message = new SmsMessage;
        $message->forceFill([
            'reference' => 'SMS-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
            'recipient' => $recipient,
            'body' => $body,
            'context' => Str::limit($context, 64, ''),
            'provider' => $preferred ?: 'pending',
            'preferred_provider' => $preferred,
            'status' => SmsMessage::QUEUED,
            'queued_at' => now(),
        ]);
        $message->save();

        return $message;
    }

    private function format(string $digits, string $provider): string
    {
        $format = (string) config('sms.providers.'.$provider.'.phone_format', 'digits');

        return $format === 'e164' ? '+'.$digits : $digits;
    }

    public function normalize(?string $phone): ?string
    {
        return $this->digits($phone);
    }

    private function digits(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '233'.substr($digits, 1);
        }

        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }
}
