<?php

namespace App\Modules\Identity\Infrastructure\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Queued e-mail verification link addressed by uuid + email hash, signed and
 * time-limited (auth.verification.expire minutes).
 */
class VerifyEmailAddress extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'uuid' => $notifiable->getRouteKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
