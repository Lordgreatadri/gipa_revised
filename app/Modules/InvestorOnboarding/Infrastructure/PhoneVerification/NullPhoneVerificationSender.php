<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification;

use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\PhoneVerificationMessage;

/**
 * Placeholder until an SMS provider is approved. Delivers nothing and,
 * deliberately, logs nothing.
 */
final class NullPhoneVerificationSender implements PhoneVerificationSender
{
    public function send(PhoneVerificationMessage $message): void
    {
        // Intentionally empty.
        logger()->info('NullPhoneVerificationSender: Message not sent.', ['message' => $message]); 
    }
}
