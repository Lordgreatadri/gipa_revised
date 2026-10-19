<?php

namespace App\Modules\InvestorOnboarding\Application\PhoneVerification;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Domain\Exceptions\EmailVerificationRequired;
use App\Modules\InvestorOnboarding\Domain\Exceptions\NotAnInvestor;
use App\Modules\InvestorOnboarding\Domain\Exceptions\PhoneAlreadyVerified;
use App\Modules\InvestorOnboarding\Domain\InvestorAuditActions;
use App\Modules\InvestorOnboarding\Domain\Models\PhoneVerificationCode;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Issues a new phone verification challenge. Any earlier unconsumed
 * challenge for the user is invalidated so only one code is live at a time.
 */
final class RequestPhoneVerification
{
    public function __construct(
        private readonly PhoneVerificationCodeGenerator $generator,
        private readonly PhoneVerificationSender $sender,
        private readonly AuditRecorder $audit,
        private readonly ConnectionInterface $db,
    ) {}

    public function handle(User $user): void
    {
        $this->guard($user);

        $code = $this->generator->generate();
        $expiresAt = Carbon::now()->addMinutes((int) config('onboarding.phone_verification.ttl_minutes', 10));

        $this->db->transaction(function () use ($user, $code, $expiresAt): void {
            PhoneVerificationCode::query()
                ->where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->delete();

            $challenge = new PhoneVerificationCode([
                'phone' => $user->phone,
                'code_hash' => Hash::make($code),
                'expires_at' => $expiresAt,
            ]);
            $challenge->user()->associate($user);
            $challenge->save();

            $this->audit->record(
                AuditEntry::for(InvestorAuditActions::PHONE_VERIFICATION_REQUESTED, $user)
                    ->causedBy($user)
                    ->withMetadata(['expires_at' => $expiresAt->toIso8601String()])
            );

            $this->sender->send(new PhoneVerificationMessage($user, $user->phone, $code, $expiresAt));
        });
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
