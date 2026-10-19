<?php

namespace App\Modules\Backup\Application;

final class ArchiveToken
{
    public static function encode(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    public static function decode(string $token): ?string
    {
        $padded = strtr($token, '-_', '+/');
        $remainder = strlen($padded) % 4;

        if ($remainder !== 0) {
            $padded .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode($padded, true);

        return is_string($decoded) && $decoded !== '' ? $decoded : null;
    }
}
