<?php

namespace App\Modules\InvestorOnboarding\Application\Listeners;

use App\Modules\InvestorOnboarding\Domain\Events\InvestorRegistered;

/**
 * Client-INV-02: the verification e-mail is a reaction to registration, not
 * part of the registration transaction. The notification itself is queued.
 */
class SendEmailVerificationOnRegistration
{
    public function handle(InvestorRegistered $event): void
    {
        $event->user->sendEmailVerificationNotification();
    }
}
