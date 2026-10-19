<?php

namespace App\Modules\InvestorOnboarding\Domain\Events;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class InvestorPhoneVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly User $user) {}
}
