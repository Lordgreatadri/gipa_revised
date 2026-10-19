<?php

namespace App\Modules\Shared\Infrastructure\Sms\Data;

final readonly class ProviderBalance
{
    /**
     * @param  list<array{service: string, remaining: int}>  $bundles
     */
    public function __construct(
        public string $provider,
        public bool $available,
        public ?float $creditBalance = null,
        public ?string $currency = null,
        public ?int $smsUnits = null,
        public array $bundles = [],
        public ?string $message = null,
    ) {}

    public static function unavailable(string $provider, string $message): self
    {
        return new self(provider: $provider, available: false, message: $message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'credit_balance' => $this->creditBalance,
            'currency' => $this->currency,
            'sms_units' => $this->smsUnits,
            'bundles' => $this->bundles,
            'message' => $this->message,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(string $provider, array $payload): self
    {
        return new self(
            provider: $provider,
            available: (bool) ($payload['available'] ?? false),
            creditBalance: isset($payload['credit_balance']) ? (float) $payload['credit_balance'] : null,
            currency: isset($payload['currency']) ? (string) $payload['currency'] : null,
            smsUnits: isset($payload['sms_units']) ? (int) $payload['sms_units'] : null,
            bundles: array_values((array) ($payload['bundles'] ?? [])),
            message: isset($payload['message']) ? (string) $payload['message'] : null,
        );
    }
}
