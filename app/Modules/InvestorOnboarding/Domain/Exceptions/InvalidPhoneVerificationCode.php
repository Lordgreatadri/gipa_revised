<?php

namespace App\Modules\InvestorOnboarding\Domain\Exceptions;

final class InvalidPhoneVerificationCode extends OnboardingException
{
    public static function make(): self
    {
        return new self('The verification code is invalid or has expired.');
    }
}
