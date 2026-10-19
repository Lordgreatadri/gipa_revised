<?php

namespace App\Modules\Identity\Application;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Rules\VerifiedInvestorRule;

/**
 * Where a freshly authenticated user lands. Investors continue through the
 * verification gate. Staff with the admin dashboard permission enter the
 * staff workspace. Staff without that permission stay on the public home page.
 */
final class PostLoginDestination
{
    public function for(User $user): string
    {
        if ($user->isInvestor()) {
            if (VerifiedInvestorRule::satisfiedBy($user) && $user->investorProfile === null) {
                return route('investor.profile');
            }

            return route('investor.dashboard');
        }

        if ($user->can(AccessPermissions::ADMIN_DASHBOARD)) {
            return route('admin.dashboard');
        }

        return route('home');
    }
}
