<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Infrastructure\Sms\Contracts\SmsProvider;
use App\Modules\Shared\Infrastructure\Sms\Providers\LogSmsProvider;
use InvalidArgumentException;

class SmsProviderRegistry
{
    /** @var array<string, SmsProviderConfig>|null */
    private ?array $records = null;

    /**
     * @return list<string>
     */
    public function available(): array
    {
        return array_keys((array) config('sms.providers', []));
    }

    public function supports(string $provider): bool
    {
        return in_array($provider, $this->available(), true);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalogue(): array
    {
        return (array) config('sms.providers', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $provider): array
    {
        if (! $this->supports($provider)) {
            throw new InvalidArgumentException('Unsupported SMS provider: '.$provider);
        }

        return (array) config('sms.providers.'.$provider);
    }

    public function configFor(string $provider): ?SmsProviderConfig
    {
        return $this->records()[$provider] ?? null;
    }

    /**
     * @return array<string, SmsProviderConfig>
     */
    public function records(): array
    {
        return $this->records ??= SmsProviderConfig::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->keyBy('provider')
            ->all();
    }

    /**
     * @return list<SmsProviderConfig>
     */
    public function usable(): array
    {
        return array_values(array_filter(
            $this->records(),
            fn (SmsProviderConfig $config): bool => $config->isUsable() && $this->supports($config->provider),
        ));
    }

    public function makeFor(SmsProviderConfig $config): SmsProvider
    {
        $adapter = (string) ($this->definition($config->provider)['adapter'] ?? LogSmsProvider::class);

        return new $adapter($config);
    }

    public function flush(): void
    {
        $this->records = null;
    }
}
