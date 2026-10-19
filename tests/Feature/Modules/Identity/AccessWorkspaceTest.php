<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Database\Seeders\AccessSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function staffWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->staff()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_staff_with_the_dashboard_permission_enter_the_admin_workspace(): void
    {
        $staff = $this->staffWith([AccessPermissions::ADMIN_DASHBOARD]);

        $this->post(route('login.store'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Staff accounts')
            ->assertSee('Administration')
            ->assertDontSee('Featured opportunities');
    }

    public function test_an_investor_cannot_open_the_admin_workspace(): void
    {
        $investor = User::factory()->create();

        $this->actingAs($investor)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_a_verified_investor_sees_the_investor_workspace(): void
    {
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::investorRole());
        $this->giveBusinessProfile($investor);

        $this->actingAs($investor)->get(route('investor.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$investor->name)
            ->assertSee('Opportunities')
            ->assertDontSee('Permissions');

        $this->actingAs($investor)->get(route('investor.opportunities'))
            ->assertOk()
            ->assertSee('No records are available yet.');
    }

    public function test_roles_can_be_created_and_are_audited(): void
    {
        $staff = $this->staffWith([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::ROLES_VIEW,
            AccessPermissions::ROLES_MANAGE,
            AccessPermissions::OPPORTUNITIES_VIEW,
        ]);

        $this->actingAs($staff)->post(route('admin.roles.store'), [
            'name' => 'Catalogue reader',
            'permissions' => [AccessPermissions::OPPORTUNITIES_VIEW],
        ])->assertRedirect();

        $role = Role::query()->where('name', 'Catalogue reader')->firstOrFail();
        $this->assertNotEmpty($role->uuid);
        $this->assertTrue($role->hasPermissionTo(AccessPermissions::OPPORTUNITIES_VIEW));
        $this->assertAuditRecorded(IdentityAuditActions::ROLE_CREATED, $role->id);
    }

    public function test_the_new_role_form_lists_permissions_to_sync(): void
    {
        $this->seed(PermissionSeeder::class);

        $staff = $this->staffWith([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::ROLES_VIEW,
            AccessPermissions::ROLES_MANAGE,
        ]);

        $this->actingAs($staff)->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Map permissions to this role')
            ->assertSee('assistant.knowledge.manage')
            ->assertSee('backups.view')
            ->assertSee('name="permissions[]"', false);
    }

    public function test_the_permission_seeder_adds_missing_defaults_and_keeps_custom_grants(): void
    {
        $role = Role::findOrCreate(AccessRoles::CONTENT_MANAGER, 'web');
        Permission::findOrCreate(AccessPermissions::AUDIT_VIEW, 'web');
        $role->givePermissionTo(AccessPermissions::AUDIT_VIEW);

        $this->seed(PermissionSeeder::class);
        $this->seed(PermissionSeeder::class);

        $fresh = $role->fresh();
        $this->assertTrue($fresh->hasPermissionTo(AccessPermissions::BACKUPS_VIEW));
        $this->assertTrue($fresh->hasPermissionTo(AccessPermissions::ASSISTANT_KNOWLEDGE_MANAGE));
        $this->assertTrue($fresh->hasPermissionTo(AccessPermissions::AUDIT_VIEW));
        $this->assertDatabaseHas('permissions', ['name' => AccessPermissions::BACKUPS_DOWNLOAD, 'guard_name' => 'web']);
    }

    public function test_the_super_administrator_role_cannot_be_deleted(): void
    {
        $staff = $this->staffWith([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::ROLES_VIEW,
            AccessPermissions::ROLES_MANAGE,
        ]);
        $role = Role::findOrCreate(AccessRoles::SUPER_ADMINISTRATOR, 'web');

        $this->actingAs($staff)
            ->from(route('admin.roles.edit', $role))
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHasErrors('role');

        $this->assertNotNull(Role::query()->where('name', AccessRoles::SUPER_ADMINISTRATOR)->first());
    }

    public function test_staff_roles_are_not_assigned_to_an_investor(): void
    {
        $staff = $this->staffWith([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::USERS_VIEW,
            AccessPermissions::USERS_ASSIGN_ROLES,
        ]);
        $investor = User::factory()->create();
        $role = Role::findOrCreate(AccessRoles::CONTENT_MANAGER, 'web');

        $this->actingAs($staff)
            ->put(route('admin.users.update', $investor), ['roles' => [$role->uuid]])
            ->assertNotFound();
    }

    public function test_the_access_seeder_creates_the_catalogue_and_a_bootstrap_administrator(): void
    {
        $this->makeCountry();

        config([
            'access.seed_demo_accounts' => false,
            'access.users' => [[
                'name' => 'Ada Admin',
                'email' => 'ada.admin@example.test',
                'password' => 'local-only-password',
                'phone' => '+233200000099',
                'role' => AccessRoles::SUPER_ADMINISTRATOR,
                'user_type' => 'staff',
                'demo' => false,
            ]],
        ]);

        $this->seed(AccessSeeder::class);

        $admin = User::query()->where('email', 'ada.admin@example.test')->firstOrFail();
        $this->assertTrue($admin->isStaff());
        $this->assertTrue($admin->hasRole(AccessRoles::SUPER_ADMINISTRATOR));
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue($admin->hasVerifiedPhone());
        $this->assertNotNull(Role::query()->where('name', AccessRoles::DISTRICT_OFFICER)->first());
        $this->assertNotNull(Role::query()->where('name', AccessRoles::REVIEWER)->first());

        $this->post(route('login.store'), [
            'email' => 'ada.admin@example.test',
            'password' => 'local-only-password',
        ])->assertRedirect(route('admin.dashboard'));
    }
}
