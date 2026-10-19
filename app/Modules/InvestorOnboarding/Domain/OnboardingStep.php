<?php

namespace App\Modules\InvestorOnboarding\Domain;

use App\Modules\Identity\Domain\Models\User;

/**
 * Derived, not stored: ADR-0010 allows onboarding progress to be represented
 * by independently tracked verification conditions. The order is fixed by
 * the confirmed client journey (email, then phone).
 */
enum OnboardingStep: string
{
    case VerifyEmail = 'verify_email';
    case VerifyPhone = 'verify_phone';

    /**
     * The next step an investor must complete, or null when fully verified.
     */
    public static function nextFor(User $user): ?self
    {
        if (! $user->hasVerifiedEmail()) {
            return self::VerifyEmail;
        }

        if (! $user->hasVerifiedPhone()) {
            return self::VerifyPhone;
        }

        return null;
    }

    public function routeName(): string
    {
        return match ($this) {
            self::VerifyEmail => 'verification.notice',
            self::VerifyPhone => 'phone.verification.notice',
        };
    }
}
