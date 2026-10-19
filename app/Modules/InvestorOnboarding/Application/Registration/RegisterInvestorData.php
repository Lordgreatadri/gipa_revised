<?php

namespace App\Modules\InvestorOnboarding\Application\Registration;

use App\Modules\Shared\Domain\Models\Country;

/**
 * Minimum registration fields approved under D-2:
 * name, email, phone, password, country.
 */
final class RegisterInvestorData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password,
        public readonly Country $country,
    ) {}
}
