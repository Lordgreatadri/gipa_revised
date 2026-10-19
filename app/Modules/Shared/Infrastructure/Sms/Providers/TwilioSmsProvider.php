<?php

namespace App\Modules\Shared\Infrastructure\Sms\Providers;

use App\Modules\Shared\Infrastructure\Sms\Data\OutgoingSms;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\Data\SmsResult;

class TwilioSmsProvider extends AbstractSmsProvider
{
    private const ACCEPTED = ['queued', 'accepted', 'scheduled', 'sending', 'sent'];

    public function name(): string
    {
        return 'twilio';
    }

    public function send(OutgoingSms $message): SmsResult
    {
        [$sid, $token] = $this->credentials();

        if ($sid === null || $token === null) {
            return SmsResult::failure('CONFIGURATION_MISSING', 'Twilio credentials are incomplete.');
        }

        $payload = [
            'From' => $message->senderId,
            'To' => $message->recipient,
            'Body' => $message->body,
        ];

        if (filled($message->callbackUrl)) {
            $payload['StatusCallback'] = $message->callbackUrl;
        }

        $response = $this->http()
            ->asForm()
            ->withBasicAuth($sid, $token)
            ->post($this->baseUrl().'/Accounts/'.$sid.'/Messages.json', $payload);

        $this->log($response->successful() ? 'info' : 'warning', 'Twilio send completed', [
            'reference' => $message->reference,
            'status_code' => $response->status(),
        ]);

        if ($response->serverError() || $response->status() === 429) {
            return SmsResult::transportFailure('PROVIDER_UNAVAILABLE', 'Twilio returned HTTP '.$response->status().'.');
        }

        $status = strtolower((string) ($response->json('status') ?? ''));

        if ($response->failed() || ! in_array($status, self::ACCEPTED, true)) {
            return SmsResult::failure(
                (string) ($response->json('code') ?? ($status ?: 'REJECTED')),
                (string) ($response->json('message') ?? 'Twilio rejected the message.'),
            );
        }

        return SmsResult::success($status, 'Accepted by Twilio.', $response->json('sid'));
    }

    public function balance(): ProviderBalance
    {
        [$sid, $token] = $this->credentials();

        if ($sid === null || $token === null) {
            return ProviderBalance::unavailable($this->name(), 'Twilio credentials are incomplete.');
        }

        $response = $this->http()->withBasicAuth($sid, $token)->get($this->baseUrl().'/Accounts/'.$sid.'/Balance.json');

        if ($response->failed()) {
            return ProviderBalance::unavailable($this->name(), 'Twilio balance lookup returned HTTP '.$response->status().'.');
        }

        return new ProviderBalance(
            provider: $this->name(),
            available: true,
            creditBalance: (float) ($response->json('balance') ?? 0),
            currency: (string) ($response->json('currency') ?? 'USD'),
            message: 'Account balance retrieved.',
        );
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function credentials(): array
    {
        $sid = $this->providerConfig->credential('account_sid');
        $token = $this->providerConfig->credential('auth_token');

        return [blank($sid) ? null : $sid, blank($token) ? null : $token];
    }
}
