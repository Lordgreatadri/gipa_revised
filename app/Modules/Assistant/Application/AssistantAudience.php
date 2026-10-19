<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Domain\Models\AssistantDocument;
use App\Modules\Identity\Domain\Models\User;

/**
 * Guests, staff, and investors who have not saved a business profile stay on
 * the public knowledge set. Opportunity titles are a separate tool gate.
 */
final class AssistantAudience
{
    public static function for(?User $user): string
    {
        if (
            $user !== null
            && $user->isInvestor()
            && $user->hasVerifiedEmail()
            && $user->hasVerifiedPhone()
            && $user->investorProfile !== null
        ) {
            return AssistantDocument::AUDIENCE_INVESTOR;
        }

        return AssistantDocument::AUDIENCE_PUBLIC;
    }
}
