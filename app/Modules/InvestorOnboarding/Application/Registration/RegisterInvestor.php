<?php

namespace App\Modules\InvestorOnboarding\Application\Registration;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorRegistered;
use App\Modules\InvestorOnboarding\Domain\InvestorAuditActions;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;

/**
 * Client-INV-01. The only self-registration path in the system and it always
 * produces an investor account (ADR-0005: staff join by invitation only).
 * Registration grants no access: e-mail and phone verification follow.
 */
final class RegisterInvestor
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly Dispatcher $events,
        private readonly ConnectionInterface $db,
    ) {}

    public function handle(RegisterInvestorData $data): User
    {
        $user = $this->db->transaction(function () use ($data): User {
            $user = new User([
                'name' => $data->name,
                'email' => mb_strtolower($data->email),
                'phone' => $data->phone,
                'password' => $data->password,
            ]);
            $user->user_type = UserType::Investor;
            $user->country()->associate($data->country);
            $user->save();
            $user->assignRole(AccessRoles::investorRole());

            $this->audit->record(
                AuditEntry::for(InvestorAuditActions::REGISTERED, $user)
                    ->causedBy($user)
                    ->withMetadata([
                        'user_type' => UserType::Investor->value,
                        'country' => $data->country->abbreviation,
                    ])
            );

            return $user;
        });

        $this->events->dispatch(new InvestorRegistered($user));

        return $user;
    }
}
