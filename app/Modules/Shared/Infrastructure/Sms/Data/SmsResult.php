<?php

namespace App\Modules\Shared\Infrastructure\Sms\Data;

final readonly class SmsResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    private function __construct(
        public bool $successful,
        public string $status,
        public string $message,
        public ?string $providerMessageId,
        public bool $retryable,
    ) {}

    public static function success(string $status, string $message, ?string $providerMessageId = null): self
    {
        return new self(true, $status, $message, $providerMessageId, false);
    }

    public static function failure(string $status, string $message, bool $retryable = false): self
    {
        return new self(false, $status, $message, null, $retryable);
    }

    public static function transportFailure(string $status, string $message): self
    {
        return new self(false, $status, $message, null, true);
    }
}
