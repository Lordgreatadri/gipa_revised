<?php

namespace App\Modules\Identity\Infrastructure\LoginHistory;

use App\Modules\Identity\Application\LoginHistory\LoginPlace;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Throwable;

/**
 * Resolves a public IP through stevebauman/location.
 * Private and reserved addresses are stored without a lookup.
 * A lookup failure leaves the place empty and does not block sign-in.
 */
final class IpApiLoginPlaceLookup
{
    public function locate(?string $ip): ?LoginPlace
    {
        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        try {
            $position = Location::get($ip);
        } catch (Throwable) {
            return null;
        }

        if (! $position instanceof Position) {
            return null;
        }

        return new LoginPlace(
            countryCode: $this->short($position->countryCode, 2),
            countryName: $this->short($position->countryName, 80),
            regionName: $this->short($position->regionName, 80),
            cityName: $this->short($position->cityName, 80),
            latitude: $this->coordinate($position->latitude),
            longitude: $this->coordinate($position->longitude),
            driver: $this->short($position->driver ?? null, 120),
        );
    }

    private function short(?string $value, int $limit): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $limit);
    }

    private function coordinate(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (string) $value;
    }
}
