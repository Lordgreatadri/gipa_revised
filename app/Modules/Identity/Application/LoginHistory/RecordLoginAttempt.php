<?php

namespace App\Modules\Identity\Application\LoginHistory;

use App\Modules\Identity\Domain\Models\LoginAttempt;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Infrastructure\LoginHistory\IpApiLoginPlaceLookup;
use Illuminate\Http\Request;

final class RecordLoginAttempt
{
    public function __construct(private readonly IpApiLoginPlaceLookup $places) {}

    public function record(string $outcome, ?User $user, ?string $email, string $guard, ?bool $remember = null): void
    {
        $request = $this->request();
        $ip = $request?->ip();
        $place = $this->places->locate(is_string($ip) ? $ip : null);
        $identifier = mb_strtolower(trim((string) ($email ?: $user?->email)));

        $attempt = new LoginAttempt;
        $attempt->forceFill([
            'user_id' => $user?->id,
            'email' => mb_substr($identifier !== '' ? $identifier : 'unknown', 0, 255),
            'outcome' => $outcome,
            'guard' => mb_substr($guard, 0, 20),
            'ip_address' => is_string($ip) && $ip !== '' ? mb_substr($ip, 0, 45) : null,
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 512) : null,
            'country_code' => $place?->countryCode,
            'country_name' => $place?->countryName,
            'region_name' => $place?->regionName,
            'city_name' => $place?->cityName,
            'latitude' => $place?->latitude,
            'longitude' => $place?->longitude,
            'location_driver' => $place?->driver,
            'remember' => $remember,
        ])->save();
    }

    private function request(): ?Request
    {
        $request = request();

        return $request instanceof Request ? $request : null;
    }
}
