<?php

namespace App\Modules\InvestorOnboarding\Application\Contracts;

use App\Modules\InvestorOnboarding\Application\PhoneVerification\PhoneVerificationMessage;

/**
 * Delivery boundary for phone verification codes (Client-INV-03).
 *
 * ADR-0005 leaves the provider open; the domain depends only on this
 * contract. Implementations must never write the code to application logs.
 */
interface PhoneVerificationSender
{
    public function send(PhoneVerificationMessage $message): void;
}
