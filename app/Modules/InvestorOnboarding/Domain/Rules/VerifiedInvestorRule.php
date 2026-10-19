<?php

namespace App\Modules\InvestorOnboarding\Domain\Rules;

use App\Modules\Identity\Domain\Models\User;

/**
 * Client-INV-04 / ADR-0011 / rules 20-ddd "Investor access invariant".
 *
 * A verified investor is an investor account whose e-mail AND phone are both
 * verified. Registration alone, or either verification alone, is not enough.
 * Every investor-protected use case must go through this rule; it must not
 * be re-implemented in Blade, JavaScript or controllers.
 */
final class VerifiedInvestorRule
{
    public static function satisfiedBy(User $user): bool
    {
        return $user->isInvestor()
            && $user->hasVerifiedEmail()
            && $user->hasVerifiedPhone();
    }
}
