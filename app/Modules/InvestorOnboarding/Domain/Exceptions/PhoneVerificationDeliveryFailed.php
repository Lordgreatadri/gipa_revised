<?php

namespace App\Modules\InvestorOnboarding\Domain\Exceptions;

final class PhoneVerificationDeliveryFailed extends OnboardingException
{
    public static function make(): self
    {
        return new self('We could not send a text message to your phone. Try again in a moment.');
    }
}
