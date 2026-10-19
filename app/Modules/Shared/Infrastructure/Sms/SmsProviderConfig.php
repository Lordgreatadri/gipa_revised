<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsProviderConfig extends Model
{
    use HasUuidRouteKey;
    use SoftDeletes;

    protected $fillable = [];

    protected $hidden = [
        'credentials',
        'webhook_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'credentials' => 'encrypted:array',
            'webhook_secret' => 'encrypted',
            'balance_snapshot' => 'array',
            'balance_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'consecutive_failures' => 'integer',
            'credit_threshold' => 'decimal:2',
            'bundle_threshold' => 'integer',
            'balance_alerted_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return (array) config('sms.providers.'.$this->provider, []);
    }

    public function displayName(): string
    {
        return $this->label ?: (string) ($this->definition()['label'] ?? ucfirst($this->provider));
    }

    public function credential(string $key, ?string $default = null): ?string
    {
        $value = ($this->credentials ?? [])[$key] ?? null;

        if (blank($value)) {
            $value = ($this->definition()['defaults'] ?? [])[$key] ?? $default;
        }

        return $value === null || $value === '' ? null : (string) $value;
    }

    public function isConfigured(): bool
    {
        foreach ($this->definition()['credentials'] ?? [] as $key => $meta) {
            if (($meta['required'] ?? false) && blank($this->credential($key))) {
                return false;
            }
        }

        return true;
    }

    public function isUsable(): bool
    {
        return $this->is_active && $this->isConfigured() && $this->deleted_at === null;
    }

    /**
     * @return list<string>
     */
    public function missingCredentials(): array
    {
        $missing = [];

        foreach ($this->definition()['credentials'] ?? [] as $key => $meta) {
            if (($meta['required'] ?? false) && blank($this->credential($key))) {
                $missing[] = (string) ($meta['label'] ?? $key);
            }
        }

        return $missing;
    }

    public function hasStoredSecret(): bool
    {
        $credentials = $this->credentials ?? [];

        foreach (['api_key', 'auth_token'] as $key) {
            if (filled($credentials[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    public function supportsBalance(): bool
    {
        return (bool) ($this->definition()['supports_balance'] ?? false);
    }

    public function supportsCallbacks(): bool
    {
        return (bool) ($this->definition()['supports_delivery_callbacks'] ?? false);
    }

    public function callbackUrl(): ?string
    {
        $name = $this->definition()['webhook_config'] ?? null;

        if (! is_string($name) || $name === '') {
            return null;
        }

        try {
            return route('webhook-client-'.$name);
        } catch (\Throwable $e) {
            logger()->channel('sms')->error('Failed to generate callback URL', ['exception' => $e]);
            return null;
        }
    }

    /**
     * The address an administrator copies into the provider console.
     * Frog's URL carries the shared token because Frog does not sign callbacks.
     */
    public function acceptedCallbackUrl(): ?string
    {
        $url = $this->callbackUrl();

        if ($url === null || $this->provider !== 'frog' || blank($this->webhook_secret)) {
            return $url;
        }

        return $url.'?token='.$this->webhook_secret;
    }
}
