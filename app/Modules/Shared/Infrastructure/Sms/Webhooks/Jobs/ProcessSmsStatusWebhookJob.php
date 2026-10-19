<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks\Jobs;

use App\Modules\Shared\Infrastructure\Sms\SmsDeliveryEvent;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

/**
 * Applies a signature-checked delivery report to the matching outbound message.
 * A delivered message is never walked backwards by a later report.
 */
class ProcessSmsStatusWebhookJob extends ProcessWebhookJob
{
    public function handle(): void
    {
        $provider = $this->provider();
        $payload = (array) ($this->webhookCall->payload ?? []);
        unset($payload['token']);
        $providerMessageId = $this->providerMessageId($provider, $payload);

        if ($providerMessageId === null) {
            $this->log('warning', 'Callback ignored: no provider message id', ['provider' => $provider]);

            return;
        }

        [$status, $rawStatus, $detail] = $this->normaliseStatus($provider, $payload);

        $message = SmsMessage::query()
            ->where('provider', $provider)
            ->where(function ($query) use ($providerMessageId): void {
                $query->where('provider_message_id', $providerMessageId)
                    ->orWhere('reference', $providerMessageId);
            })
            ->latest('id')
            ->first();

        $event = new SmsDeliveryEvent;
        $event->forceFill([
            'sms_message_id' => $message?->id,
            'provider' => $provider,
            'provider_message_id' => Str::limit($providerMessageId, 190, ''),
            'status' => $status ?? 'unrecognised',
            'raw_status' => Str::limit($rawStatus, 60, ''),
            'detail' => $detail ? Str::limit($detail, 190, '') : null,
            'webhook_call_id' => $this->webhookCall->id,
            'payload' => $payload,
            'occurred_at' => $this->occurredAt($provider, $payload),
        ])->save();

        if ($message === null || $status === null) {
            $this->log('info', 'Delivery report stored', [
                'provider' => $provider,
                'matched' => $message !== null,
            ]);

            return;
        }

        if (! $this->mayAdvance($message, $status)) {
            return;
        }

        $failure = in_array($status, [SmsMessage::FAILED, SmsMessage::UNDELIVERED], true);

        $message->forceFill([
            'status' => $status,
            'error' => $failure ? Str::limit($detail ?: 'The provider reported a delivery failure.', 500, '') : null,
            'sent_at' => $message->sent_at ?: now(),
            'delivered_at' => $status === SmsMessage::DELIVERED ? ($message->delivered_at ?: now()) : $message->delivered_at,
        ])->save();

        $this->log('info', 'Delivery report applied', [
            'provider' => $provider,
            'reference' => $message->reference,
            'status' => $status,
        ]);
    }

    private function mayAdvance(SmsMessage $message, string $status): bool
    {
        if ($message->status === SmsMessage::DELIVERED && $status !== SmsMessage::DELIVERED) {
            return false;
        }

        if ($status === SmsMessage::QUEUED && $message->status !== SmsMessage::QUEUED) {
            return false;
        }

        return true;
    }

    private function provider(): string
    {
        $name = (string) $this->webhookCall->name;

        foreach ((array) config('sms.providers', []) as $key => $definition) {
            if (($definition['webhook_config'] ?? null) === $name) {
                return (string) $key;
            }
        }

        return $name;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function providerMessageId(string $provider, array $payload): ?string
    {
        foreach ((array) config('sms.providers.'.$provider.'.callback.id_fields', []) as $key) {
            $value = Arr::get($payload, $key);

            if (filled($value) && is_scalar($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: ?string, 1: string, 2: ?string}
     */
    private function normaliseStatus(string $provider, array $payload): array
    {
        $callback = (array) config('sms.providers.'.$provider.'.callback', []);
        $raw = '';

        foreach ((array) ($callback['status_fields'] ?? []) as $field) {
            $value = Arr::get($payload, $field);

            if (filled($value) && is_scalar($value)) {
                $raw = (string) $value;
                break;
            }
        }

        $detail = '';

        foreach ((array) ($callback['detail_fields'] ?? []) as $field) {
            $value = Arr::get($payload, $field);

            if (filled($value) && is_scalar($value)) {
                $detail = (string) $value;
                break;
            }
        }

        $status = null;
        $needle = strtolower($raw);

        foreach ((array) ($callback['statuses'] ?? []) as $normal => $aliases) {
            if (in_array($needle, array_map('strtolower', (array) $aliases), true)) {
                $status = (string) $normal;
                break;
            }
        }

        return [$status, $raw, $detail !== '' ? $detail : null];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function occurredAt(string $provider, array $payload): Carbon
    {
        $field = (string) config('sms.providers.'.$provider.'.callback.occurred_field', '');
        $raw = $field !== '' ? Arr::get($payload, $field) : null;

        try {
            return $raw ? Carbon::parse((string) $raw) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('sms-webhooks')->log($level, $message, $context + [
            'webhook_call_id' => $this->webhookCall->id,
        ]);
    }
}
