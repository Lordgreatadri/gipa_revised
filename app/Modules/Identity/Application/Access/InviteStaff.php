<?php

namespace App\Modules\Identity\Application\Access;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Infrastructure\Notifications\ResetPasswordLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class InviteStaff
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly ManageAccess $access,
    ) {}

    /**
     * @param  array{name: string, email: string, phone: string, role: string, districts?: list<string>}  $data
     */
    public function invite(User $actor, array $data): User
    {
        if (! $actor->can(AccessPermissions::USERS_ASSIGN_ROLES)) {
            throw new AuthorizationException;
        }

        $role = Role::query()->where('uuid', $data['role'])->firstOrFail();

        if ($role->name === AccessRoles::INVESTOR || ($role->name === AccessRoles::SUPER_ADMINISTRATOR && ! $actor->hasRole(AccessRoles::SUPER_ADMINISTRATOR))) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $data, $role): User {
            $user = new User;
            $user->forceFill([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(40)),
                'user_type' => UserType::Staff,
                'email_verified_at' => now(),
            ]);
            $user->save();

            $this->access->assignRoles($actor, $user, [$role->uuid]);
            $districts = District::query()->whereIn('uuid', $data['districts'] ?? [])->get();
            $user->districts()->sync($districts->modelKeys());

            $this->audit->record(
                AuditEntry::for(IdentityAuditActions::STAFF_INVITED, $user)
                    ->causedBy($actor)
                    ->withMetadata(['role' => $role->name, 'districts' => $districts->pluck('code')->all()])
            );

            DB::afterCommit(function () use ($user): void {
                $user->notify(new ResetPasswordLink(Password::broker()->createToken($user)));
            });

            return $user;
        });
    }
}
