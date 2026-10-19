<?php

namespace App\Modules\InvestorOnboarding\Application\PhoneVerification;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Carbon;

final class PhoneVerificationMessage
{
    public function __construct(
        public readonly User $user,
        public readonly string $phone,
        public readonly string $code,
        public readonly Carbon $expiresAt,
    ) {}

    /**
     * Debug-safe representation: never includes the code.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return [
            'user' => $this->user->uuid,
            'phone' => $this->phone,
            'code' => '[REDACTED]',
            'expiresAt' => $this->expiresAt->toIso8601String(),
        ];
    }
}
