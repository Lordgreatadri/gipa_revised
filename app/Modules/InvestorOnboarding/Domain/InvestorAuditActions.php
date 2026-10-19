<?php

namespace App\Modules\InvestorOnboarding\Domain;

final class InvestorAuditActions
{
    public const REGISTERED = 'investor.registered';

    public const PHONE_VERIFICATION_REQUESTED = 'investor.phone_verification_requested';

    public const PHONE_VERIFICATION_FAILED = 'investor.phone_verification_failed';

    public const PHONE_VERIFIED = 'investor.phone_verified';
}
