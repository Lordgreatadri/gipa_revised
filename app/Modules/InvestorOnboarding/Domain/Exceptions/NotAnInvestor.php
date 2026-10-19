<?php

namespace App\Modules\InvestorOnboarding\Domain\Exceptions;

final class NotAnInvestor extends OnboardingException
{
    public static function make(): self
    {
        return new self('Phone verification is part of investor onboarding and is not available for this account.');
    }
}
