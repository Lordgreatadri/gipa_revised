<?php

namespace App\Modules\Shared\Infrastructure\Api;

use App\Modules\Identity\Domain\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Throwable;

final class JwtTokenIssuer
{
    /**
     * @return array{token_type: string, access_token: string, expires_in: int, refresh_token: string}
     */
    public function issue(User $user): array
    {
        $ttl = max(1, (int) config('jwt.access_ttl_minutes', 15)) * 60;
        $now = time();
        $access = JWT::encode([
            'iss' => (string) config('app.url'),
            'sub' => $user->uuid,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => (string) Str::uuid(),
            'typ' => 'access',
        ], $this->key(), 'HS256');

        $plain = Str::random(64);
        $refresh = new ApiRefreshToken;
        $refresh->forceFill([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addDays(max(1, (int) config('jwt.refresh_ttl_days', 30))),
        ]);
        $refresh->save();

        return [
            'token_type' => 'Bearer',
            'access_token' => $access,
            'expires_in' => $ttl,
            'refresh_token' => $plain,
        ];
    }

    public function userFromAccessToken(string $jwt): ?User
    {
        try {
            $claims = JWT::decode($jwt, new Key($this->key(), 'HS256'));
        } catch (Throwable) {
            return null;
        }

        if (($claims->typ ?? null) !== 'access' || ! is_string($claims->sub ?? null)) {
            return null;
        }

        return User::query()->where('uuid', $claims->sub)->first();
    }

    /**
     * @return array{token_type: string, access_token: string, expires_in: int, refresh_token: string}
     */
    public function refresh(string $plain): array
    {
        $row = ApiRefreshToken::query()->where('token_hash', hash('sha256', $plain))->first();

        if ($row === null || $row->revoked_at !== null || $row->expires_at->isPast()) {
            abort(401, 'The refresh token is invalid.');
        }

        $user = $row->user;

        if (! $user instanceof User) {
            abort(401, 'The refresh token is invalid.');
        }

        $row->forceFill(['revoked_at' => now()])->save();

        return $this->issue($user);
    }

    public function revoke(string $plain): void
    {
        ApiRefreshToken::query()
            ->where('token_hash', hash('sha256', $plain))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    private function key(): string
    {
        $key = (string) config('jwt.signing_key');

        if ($key === '') {
            abort(503, 'API signing is not configured.');
        }

        return $key;
    }
}
