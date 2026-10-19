<?php

namespace App\Modules\Identity\Domain\Enums;

/**
 * User population (ADR-0005). Investors self-register; staff join only by
 * invitation. Staff roles and permissions are handled by Spatie permission,
 * not by this discriminator.
 */
enum UserType: string
{
    case Investor = 'investor';
    case Staff = 'staff';
}
