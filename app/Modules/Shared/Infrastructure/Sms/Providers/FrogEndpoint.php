<?php

namespace App\Modules\Shared\Infrastructure\Sms\Providers;

/**
 * Frog's documented base is https://frogapi.wigal.com.gh/api/v3.
 * An administrator may store the host only or the full /api/v3 prefix.
 * Either value must still call /sms/send and /balance, never a doubled path.
 */
final class FrogEndpoint
{
    public static function url(string $base, string $path): string
    {
        $base = rtrim(trim($base), '/');
        $base = (string) preg_replace('#/(sms/send|balance)$#', '', $base);
        $path = ltrim($path, '/');

        if (preg_match('#/api/v3$#', $base) === 1) {
            return $base.'/'.$path;
        }

        return $base.'/api/v3/'.$path;
    }
}
