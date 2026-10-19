<?php

namespace App\Modules\Shared\Infrastructure\Sms\Providers;

use App\Modules\Shared\Infrastructure\Sms\Data\OutgoingSms;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\Data\SmsResult;

class FrogSmsProvider extends AbstractSmsProvider
{
    private const ACCEPTED = ['ACCEPTD', 'SUCCESS'];

    public function name(): string
    {
        return 'frog';
    }

    public function send(OutgoingSms $message): SmsResult
    {
        $headers = $this->headers();

        if ($headers === null) {
            return SmsResult::failure('CONFIGURATION_MISSING', 'Frog credentials are incomplete.');
        }

        $response = $this->http()->asJson()->withHeaders($headers)->post(
            FrogEndpoint::url($this->baseUrl(), 'sms/send'),
            [
                'senderid' => $message->senderId,
                'destinations' => [[
                    'destination' => $message->recipient,
                    'msgid' => $message->reference,
                ]],
                'message' => $message->body,
                'smstype' => 'text',
            ],
        );

        $this->log($response->successful() ? 'info' : 'warning', 'Frog send completed', [
            'reference' => $message->reference,
            'status_code' => $response->status(),
        ]);

        if ($response->serverError() || $response->status() === 429 || $response->status() === 405) {
            return SmsResult::transportFailure('PROVIDER_UNAVAILABLE', 'Frog returned HTTP '.$response->status().'.');
        }

        $status = strtoupper((string) ($response->json('status') ?? ''));

        if ($response->failed() || ! in_array($status, self::ACCEPTED, true)) {
            return SmsResult::failure($status ?: 'REJECTED', (string) ($response->json('message') ?? 'Frog rejected the message.'));
        }

        return SmsResult::success($status, 'Accepted by Frog.', $message->reference);
    }

    public function balance(): ProviderBalance
    {
        $headers = $this->headers();

        if ($headers === null) {
            return ProviderBalance::unavailable($this->name(), 'Frog credentials are incomplete.');
        }

        $response = $this->http()->withHeaders($headers)->get(FrogEndpoint::url($this->baseUrl(), 'balance'));

        if ($response->failed()) {
            return ProviderBalance::unavailable($this->name(), 'Frog balance lookup returned HTTP '.$response->status().'.');
        }

        $data = (array) $response->json('data', []);
        $bundles = (array) ($data['bundles'] ?? []);
        $formatted = [];

        foreach ($bundles as $service => $quantity) {
            if (! is_scalar($quantity)) {
                continue;
            }

            $formatted[] = ['service' => (string) $service, 'remaining' => (int) $quantity];
        }

        return new ProviderBalance(
            provider: $this->name(),
            available: true,
            creditBalance: (float) ($data['cashbalance'] ?? 0),
            currency: 'GHS',
            smsUnits: isset($bundles['SMS']) ? (int) $bundles['SMS'] : 0,
            bundles: $formatted,
            message: 'Balance retrieved.',
        );
    }

    /**
     * @return array<string, string>|null
     */
    private function headers(): ?array
    {
        $apiKey = $this->providerConfig->credential('api_key');
        $username = $this->providerConfig->credential('username');

        if (blank($apiKey) || blank($username)) {
            return null;
        }

        return ['API-KEY' => $apiKey, 'USERNAME' => $username];
    }
}
