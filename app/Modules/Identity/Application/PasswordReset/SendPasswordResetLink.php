<?php

namespace App\Modules\Identity\Application\PasswordReset;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\IdentityAuditActions;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;

/**
 * Requests a password-reset link. The outcome is recorded in the audit store
 * for investigations but is deliberately not returned to the caller, so the
 * UI can answer identically whether or not the account exists.
 */
class SendPasswordResetLink
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function handle(string $email): void
    {
        $email = mb_strtolower(trim($email));

        $status = Password::broker()->sendResetLink(['email' => $email]);

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::PASSWORD_RESET_REQUESTED)
                ->causedBy(null)
                ->withMetadata([
                    'identifier' => mb_substr($email, 0, 255),
                    'outcome' => match ($status) {
                        PasswordBroker::RESET_LINK_SENT => 'link_sent',
                        PasswordBroker::RESET_THROTTLED => 'throttled',
                        PasswordBroker::INVALID_USER => 'unknown_account',
                        default => 'not_sent',
                    },
                ])
        );
    }
}
