<?php

namespace App\Modules\InvestorOnboarding\Domain\Exceptions;

final class PhoneAlreadyVerified extends OnboardingException
{
    public static function make(): self
    {
        return new self('This phone number is already verified.');
    }
}
