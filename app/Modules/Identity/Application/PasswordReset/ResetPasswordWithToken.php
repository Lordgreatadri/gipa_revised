<?php

namespace App\Modules\Identity\Application\PasswordReset;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Completes a password reset with a single-use token. On success the
 * remember-token is rotated so "remembered" sessions on other devices are
 * invalidated, and the framework PasswordReset event is fired (audited by
 * RecordAuthenticationAudit).
 */
class ResetPasswordWithToken
{
    /**
     * @param  array{email: string, password: string, password_confirmation?: string, token: string}  $credentials
     * @return string one of the PasswordBroker::* status constants
     */
    public function handle(array $credentials): string
    {
        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        return Password::broker()->reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }

    public static function succeeded(string $status): bool
    {
        return $status === PasswordBroker::PASSWORD_RESET;
    }
}
