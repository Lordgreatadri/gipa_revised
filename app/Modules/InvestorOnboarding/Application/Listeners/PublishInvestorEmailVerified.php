<?php

namespace App\Modules\InvestorOnboarding\Application\Listeners;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorEmailVerified;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Translates the framework's account-level Verified event into the
 * onboarding domain event for investor accounts. Audit of the verification
 * itself is owned by Identity (account.email_verified).
 */
class PublishInvestorEmailVerified
{
    public function __construct(private readonly Dispatcher $events) {}

    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user instanceof User && $user->isInvestor()) {
            $this->events->dispatch(new InvestorEmailVerified($user));
        }
    }
}
