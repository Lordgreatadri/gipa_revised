<?php

namespace App\Modules\Identity\Application\Listeners;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\IdentityAuditActions;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;

/**
 * ADR-0009 "login/security events". Credentials are never stored: only the
 * attempted identifier is kept for failed attempts so investigations can
 * correlate abuse against an account.
 */
class RecordAuthenticationAudit
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [$this, 'onLogin']);
        $events->listen(Failed::class, [$this, 'onFailed']);
        $events->listen(Lockout::class, [$this, 'onLockout']);
        $events->listen(Logout::class, [$this, 'onLogout']);
        $events->listen(Verified::class, [$this, 'onVerified']);
        $events->listen(PasswordReset::class, [$this, 'onPasswordReset']);
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        $user = $event->user;

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::PASSWORD_RESET, $user instanceof Model ? $user : null)
                ->causedBy($user)
        );
    }

    public function onLogin(Login $event): void
    {
        $user = $event->user;

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::LOGIN_SUCCEEDED, $user instanceof Model ? $user : null)
                ->causedBy($user)
                ->withMetadata(['guard' => $event->guard, 'remember' => $event->remember])
        );
    }

    public function onFailed(Failed $event): void
    {
        $user = $event->user;

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::LOGIN_FAILED, $user instanceof Model ? $user : null)
                ->causedBy(null)
                ->withMetadata([
                    'guard' => $event->guard,
                    'identifier' => $this->identifier($event->credentials),
                ])
        );
    }

    public function onLockout(Lockout $event): void
    {
        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::LOGIN_LOCKED_OUT)
                ->causedBy(null)
                ->withMetadata(['identifier' => $this->identifier($event->request->only('email'))])
        );
    }

    public function onLogout(Logout $event): void
    {
        $user = $event->user;

        if ($user === null) {
            return;
        }

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::LOGOUT, $user instanceof Model ? $user : null)
                ->causedBy($user)
                ->withMetadata(['guard' => $event->guard])
        );
    }

    public function onVerified(Verified $event): void
    {
        $user = $event->user;

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::EMAIL_VERIFIED, $user instanceof Model ? $user : null)
                ->causedBy($user)
        );
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function identifier(array $credentials): ?string
    {
        $email = $credentials['email'] ?? null;

        return is_string($email) ? mb_substr(mb_strtolower($email), 0, 255) : null;
    }
}
