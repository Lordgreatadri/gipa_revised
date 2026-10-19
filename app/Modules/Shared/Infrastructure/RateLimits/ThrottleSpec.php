<?php

namespace App\Modules\Shared\Infrastructure\RateLimits;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Parses the "<attempts>,<minutes>" strings used in config into a Limit.
 * Each named limiter gets its own bucket per user (or per IP for guests),
 * so one flow cannot consume another flow's attempts.
 */
final class ThrottleSpec
{
    public static function limit(string $spec, string $bucket): Limit
    {
        [$attempts, $minutes] = self::parse($spec);

        return Limit::perMinutes($minutes, $attempts)->by($bucket);
    }

    /**
     * @return array{int, int}
     */
    public static function parse(string $spec): array
    {
        $parts = array_map('trim', explode(',', $spec));

        if (count($parts) !== 2 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1]) || (int) $parts[0] < 1 || (int) $parts[1] < 1) {
            throw new InvalidArgumentException("Invalid throttle specification [{$spec}]; expected \"attempts,minutes\".");
        }

        return [(int) $parts[0], (int) $parts[1]];
    }

    public static function bucketFor(Request $request): string
    {
        $user = $request->user();

        return $user !== null ? 'user:'.$user->getAuthIdentifier() : 'ip:'.$request->ip();
    }
}
