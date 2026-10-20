<?php

namespace App\Modules\Identity\Application\LoginHistory;

final class LoginPlace
{
    public function __construct(
        public readonly ?string $countryCode,
        public readonly ?string $countryName,
        public readonly ?string $regionName,
        public readonly ?string $cityName,
        public readonly ?string $latitude,
        public readonly ?string $longitude,
        public readonly ?string $driver,
    ) {}
}
