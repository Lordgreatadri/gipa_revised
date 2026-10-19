<?php

namespace App\Modules\Assistant\Application\Support;

final class Text
{
    public static function clean(string $value): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value);
    }

    /**
     * @return list<string>
     */
    public static function tokens(string $value): array
    {
        $value = mb_strtolower(self::clean($value));
        $parts = preg_split('/[^a-z0-9]+/u', $value) ?: [];

        return array_values(array_filter(
            $parts,
            fn (string $token): bool => mb_strlen($token) >= 3,
        ));
    }

    public static function estimateTokens(string $value): int
    {
        $length = mb_strlen(trim($value));

        return $length === 0 ? 0 : (int) max(1, ceil($length / 4));
    }
}
