<?php

namespace App\Modules\InvestorOnboarding\Domain\Exceptions;

final class EmailVerificationRequired extends OnboardingException
{
    public static function make(): self
    {
        return new self('Verify your e-mail address before verifying your phone number.');
    }
}
