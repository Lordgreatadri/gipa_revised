<?php

namespace App\Modules\InvestorOnboarding\Application\PhoneVerification;

/**
 * Cryptographically random numeric code of the configured length.
 */
final class PhoneVerificationCodeGenerator
{
    public function generate(): string
    {
        $length = max(4, (int) config('onboarding.phone_verification.code_length', 6));

        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}
