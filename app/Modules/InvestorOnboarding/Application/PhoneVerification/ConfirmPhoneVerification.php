<?php

namespace App\Modules\InvestorOnboarding\Application\PhoneVerification;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorPhoneVerified;
use App\Modules\InvestorOnboarding\Domain\Exceptions\EmailVerificationRequired;
use App\Modules\InvestorOnboarding\Domain\Exceptions\InvalidPhoneVerificationCode;
use App\Modules\InvestorOnboarding\Domain\Exceptions\NotAnInvestor;
use App\Modules\InvestorOnboarding\Domain\Exceptions\PhoneAlreadyVerified;
use App\Modules\InvestorOnboarding\Domain\InvestorAuditActions;
use App\Modules\InvestorOnboarding\Domain\Models\PhoneVerificationCode;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Confirms the phone verification challenge and records the phone-verified
 * fact. Sets only phone_verified_at; e-mail verification is a separate fact.
 */
final class ConfirmPhoneVerification
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly Dispatcher $events,
        private readonly ConnectionInterface $db,
    ) {}

    public function handle(User $user, string $code): void
    {
        $this->guard($user);

        $challenge = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->active()
            ->latest('id')
            ->first();

        if ($challenge === null || ! Hash::check($code, $challenge->code_hash)) {
            $this->recordFailure($user, $challenge);

            throw InvalidPhoneVerificationCode::make();
        }

        $this->db->transaction(function () use ($user, $challenge): void {
            $challenge->forceFill(['consumed_at' => now()])->save();

            $user->markPhoneAsVerified();

            $this->audit->record(
                AuditEntry::for(InvestorAuditActions::PHONE_VERIFIED, $user)
                    ->causedBy($user)
                    ->withChange(['phone_verified_at' => null], ['phone_verified_at' => $user->phone_verified_at?->toIso8601String()])
            );
        });

        $this->events->dispatch(new InvestorPhoneVerified($user));
    }

    private function recordFailure(User $user, ?PhoneVerificationCode $challenge): void
    {
        $challenge?->increment('failed_attempts');

        $this->audit->record(
            AuditEntry::for(InvestorAuditActions::PHONE_VERIFICATION_FAILED, $user)
                ->causedBy($user)
                ->withMetadata(['active_challenge' => $challenge !== null])
        );
    }

    private function guard(User $user): void
    {
        if (! $user->isInvestor()) {
            throw NotAnInvestor::make();
        }

        if (! $user->hasVerifiedEmail()) {
            throw EmailVerificationRequired::make();
        }

        if ($user->hasVerifiedPhone()) {
            throw PhoneAlreadyVerified::make();
        }
    }
}
