<?php

namespace App\Modules\Audit\Application;

/**
 * Removes secret material from audit payloads (ADR-0009 "Never record").
 * Applied recursively; key comparison is case-insensitive.
 */
final class SensitiveDataRedactor
{
    /** @var array<int, string> */
    private readonly array $keys;

    private readonly string $marker;

    /**
     * @param  array<int, string>|null  $keys
     */
    public function __construct(?array $keys = null, ?string $marker = null)
    {
        $this->keys = array_map('strtolower', $keys ?? (array) config('audit.redacted_keys', []));
        $this->marker = $marker ?? (string) config('audit.redaction_marker', '[REDACTED]');
    }

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public function redact(array $payload): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $this->keys, true)) {
                $result[$key] = $this->marker;

                continue;
            }

            $result[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $result;
    }
}
