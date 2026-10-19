<?php

namespace App\Modules\Shared\Infrastructure\Sms\Providers;

use App\Modules\Shared\Infrastructure\Sms\Contracts\SmsProvider;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;

abstract class AbstractSmsProvider implements SmsProvider
{
    public function __construct(protected readonly SmsProviderConfig $providerConfig) {}

    public function config(): SmsProviderConfig
    {
        return $this->providerConfig;
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('sms.http.timeout', 12))
            ->connectTimeout((int) config('sms.http.connect_timeout', 5));
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->providerConfig->credential('base_url'), '/');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        logger()->channel('sms')->log($level, $message, [
            'provider' => $this->name(),
            'reference' => $context['reference'] ?? null,
            'destination' => $context['destination'] ?? null,
            'status_code' => $context['status_code'] ?? null,
            'response' => $context['response'] ?? null,
        ]);
    }
}
