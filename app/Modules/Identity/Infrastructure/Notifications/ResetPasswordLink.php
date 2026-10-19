<?php

namespace App\Modules\Identity\Infrastructure\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued password-reset link. The token is single-use and expires after
 * auth.passwords.users.expire minutes; it is never written to the audit store.
 */
class ResetPasswordLink extends ResetPassword implements ShouldQueue
{
    use Queueable;
}
