<?php

namespace App\Modules\Identity\Application\Access;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Administrative changes to roles, permissions and role assignment.
 * Server-side checks stay here; the Blade screens only collect input.
 */
final class ManageAccess
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @param  list<string>  $permissions
     */
    public function createRole(User $actor, string $name, array $permissions): Role
    {
        $this->guardPermissions($permissions);

        $role = DB::transaction(function () use ($actor, $name, $permissions): Role {
            $role = Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);

            $this->audit->record(
                AuditEntry::for(IdentityAuditActions::ROLE_CREATED, $role)
                    ->causedBy($actor)
                    ->withMetadata([
                        'role' => $role->name,
                        'permissions' => $permissions,
                    ])
            );

            return $role;
        });

        return $role->refresh();
    }

    /**
     * @param  list<string>  $permissions
     */
    public function updateRole(User $actor, Role $role, string $name, array $permissions): Role
    {
        if ($role->name === AccessRoles::SUPER_ADMINISTRATOR && $name !== $role->name) {
            throw ValidationException::withMessages([
                'name' => 'The Super Administrator role name is reserved.',
            ]);
        }

        if ($role->name === AccessRoles::SUPER_ADMINISTRATOR && ! in_array(AccessPermissions::ROLES_MANAGE, $permissions, true)) {
            throw ValidationException::withMessages([
                'permissions' => 'The Super Administrator role must keep role management.',
            ]);
        }

        $this->guardPermissions($permissions);

        return DB::transaction(function () use ($actor, $role, $name, $permissions): Role {
            $before = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
            ];

            $role->name = $name;
            $role->save();
            $role->syncPermissions($permissions);

            $this->audit->record(
                AuditEntry::for(IdentityAuditActions::ROLE_UPDATED, $role)
                    ->causedBy($actor)
                    ->withChange($before, [
                        'name' => $role->name,
                        'permissions' => $permissions,
                    ])
            );

            return $role->refresh();
        });
    }

    public function deleteRole(User $actor, Role $role): void
    {
        if ($role->name === AccessRoles::SUPER_ADMINISTRATOR) {
            throw ValidationException::withMessages([
                'role' => 'The Super Administrator role cannot be deleted.',
            ]);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Remove this role from its users before deleting it.',
            ]);
        }

        DB::transaction(function () use ($actor, $role): void {
            $name = $role->name;
            $role->delete();

            $this->audit->record(
                AuditEntry::for(IdentityAuditActions::ROLE_DELETED)
                    ->causedBy($actor)
                    ->withMetadata(['role' => $name])
            );
        });
    }

    public function createPermission(User $actor, string $name): Permission
    {
        $permission = Permission::query()->create([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::PERMISSION_CREATED, $permission)
                ->causedBy($actor)
                ->withMetadata(['permission' => $permission->name])
        );

        return $permission;
    }

    public function deletePermission(User $actor, Permission $permission): void
    {
        if ($permission->roles()->exists()) {
            throw ValidationException::withMessages([
                'permission' => 'Remove this permission from its roles before deleting it.',
            ]);
        }

        $name = $permission->name;
        $permission->delete();

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::PERMISSION_DELETED)
                ->causedBy($actor)
                ->withMetadata(['permission' => $name])
        );
    }

    /**
     * @param  list<string>  $roleUuids
     */
    public function assignRoles(User $actor, User $subject, array $roleUuids): void
    {
        if (! $subject->isStaff()) {
            throw ValidationException::withMessages([
                'roles' => 'Investor accounts keep the Investor role. Staff roles are assigned to staff accounts.',
            ]);
        }

        $roles = Role::query()->whereIn('uuid', $roleUuids)->get();

        if ($roles->count() !== count(array_unique($roleUuids))) {
            throw ValidationException::withMessages([
                'roles' => 'One or more roles are not available.',
            ]);
        }

        if ($roles->contains(fn (Role $role) => $role->name === AccessRoles::INVESTOR)) {
            throw ValidationException::withMessages([
                'roles' => 'The Investor role is reserved for investor accounts.',
            ]);
        }

        $before = $subject->roles->pluck('name')->sort()->values()->all();

        $subject->syncRoles($roles);

        $this->audit->record(
            AuditEntry::for(IdentityAuditActions::USER_ROLES_ASSIGNED, $subject)
                ->causedBy($actor)
                ->withChange(
                    ['roles' => $before],
                    ['roles' => $roles->pluck('name')->sort()->values()->all()],
                )
        );
    }

    /**
     * @param  list<string>  $permissions
     */
    private function guardPermissions(array $permissions): void
    {
        $known = Permission::query()->where('guard_name', 'web')->whereIn('name', $permissions)->pluck('name');

        if ($known->count() !== count(array_unique($permissions))) {
            throw ValidationException::withMessages([
                'permissions' => 'One or more permissions are not available.',
            ]);
        }
    }
}
