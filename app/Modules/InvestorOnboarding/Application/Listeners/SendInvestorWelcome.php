<?php

namespace App\Modules\InvestorOnboarding\Application\Listeners;

use App\Modules\InvestorOnboarding\Domain\Events\InvestorPhoneVerified;
use App\Modules\InvestorOnboarding\Infrastructure\Notifications\InvestorAccountVerified;

/**
 * Phone confirmation is allowed only after e-mail verification, so this
 * event means both checks have succeeded.
 */
class SendInvestorWelcome
{
    public function handle(InvestorPhoneVerified $event): void
    {
        $user = $event->user;

        if (! $user->isInvestor() || ! $user->hasVerifiedEmail() || ! $user->hasVerifiedPhone()) {
            return;
        }

        $user->notify(new InvestorAccountVerified);
    }
}
