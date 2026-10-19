<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessRoles::defaults() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            if ($role->wasRecentlyCreated || $role->permissions()->doesntExist()) {
                $role->syncPermissions($permissions);
            } else {
                $held = $role->permissions()->pluck('name')->all();
                $missing = array_values(array_diff($permissions, $held));

                if ($missing !== []) {
                    $role->givePermissionTo($missing);
                }
            }
        }

        $this->backfillRouteKeys();

        $country = Country::query()->where('abbreviation', 'GH')->first();

        if ($country === null) {
            $this->command?->warn('Access accounts were skipped because Ghana is not in the countries table.');

            return;
        }

        $includeDemo = (bool) config('access.seed_demo_accounts');

        foreach ((array) config('access.users', []) as $account) {
            if (($account['demo'] ?? false) && ! $includeDemo) {
                continue;
            }

            $email = mb_strtolower(trim((string) ($account['email'] ?? '')));
            $password = (string) ($account['password'] ?? '');

            if ($email === '' || $password === '') {
                continue;
            }

            $this->upsertAccount($account, $email, $password, $country);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function upsertAccount(array $account, string $email, string $password, Country $country): void
    {
        $user = User::query()->where('email', $email)->first() ?? new User;
        $isNew = ! $user->exists;

        if ($isNew) {
            $phone = (string) $account['phone'];

            if (User::query()->where('phone', $phone)->exists()) {
                $this->command?->warn("Skipped {$email} because {$phone} is already used.");

                return;
            }

            $user->phone = $phone;
            $user->user_type = ($account['user_type'] ?? 'staff') === 'investor'
                ? UserType::Investor
                : UserType::Staff;
            $user->country()->associate($country);
        }

        $user->name = (string) $account['name'];
        $user->email = $email;
        $user->password = $password;
        $user->email_verified_at ??= now();
        $user->phone_verified_at ??= now();
        $user->save();

        if ($isNew || $user->roles()->doesntExist()) {
            $user->syncRoles([(string) $account['role']]);
        }
    }

    /**
     * Route keys are assigned in creating events. A previous seed that ran
     * with events suppressed can leave null UUIDs, which would publish no
     * stable admin URL. Fill those in place.
     */
    private function backfillRouteKeys(): void
    {
        foreach ([Permission::query(), Role::query()] as $query) {
            foreach ($query->whereNull('uuid')->cursor() as $record) {
                $record->forceFill(['uuid' => (string) Str::uuid()])->saveQuietly();
            }
        }
    }
}
