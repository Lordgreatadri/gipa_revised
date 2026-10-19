<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Keeps the permission catalogue in one place.
 * updateOrCreate inserts a missing permission and leaves an existing row in place.
 * Existing roles receive only the defaults they do not already hold, so a
 * custom grant is not removed and a custom denial is not overwritten by a full sync.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessPermissions::ALL as $name) {
            Permission::query()->updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        foreach (AccessRoles::defaults() as $roleName => $permissions) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

            if ($role === null) {
                continue;
            }

            $held = $role->permissions()->pluck('name')->all();
            $missing = array_values(array_diff($permissions, $held));

            if ($missing !== []) {
                $role->givePermissionTo($missing);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
