<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\LoginAttempt;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Tests\TestCase;

class LoginHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::LOGINS_VIEW,
            AccessPermissions::AUDIT_VIEW,
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    public function test_a_successful_sign_in_records_the_address_browser_and_location(): void
    {
        Location::fake([
            '8.8.8.8' => Position::make([
                'countryName' => 'Ghana',
                'countryCode' => 'GH',
                'regionName' => 'Greater Accra',
                'cityName' => 'Accra',
                'latitude' => '5.603700',
                'longitude' => '-0.187000',
                'driver' => 'test',
            ]),
        ]);

        $user = User::factory()->create(['email' => 'investor@example.com']);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->withHeader('User-Agent', 'IOMP-Test-Agent')
            ->post(route('login.store'), [
                'email' => 'Investor@Example.com',
                'password' => 'password',
                'remember' => '1',
            ])->assertRedirect();

        $attempt = LoginAttempt::query()->where('email', 'investor@example.com')->firstOrFail();
        $this->assertSame(LoginAttempt::OUTCOME_SUCCESS, $attempt->outcome);
        $this->assertSame($user->id, $attempt->user_id);
        $this->assertSame('8.8.8.8', $attempt->ip_address);
        $this->assertSame('IOMP-Test-Agent', $attempt->user_agent);
        $this->assertSame('Accra, Greater Accra, Ghana', $attempt->placeLabel());
        $this->assertSame('GH', $attempt->country_code);
        $this->assertTrue($attempt->remember);
        $this->assertStringNotContainsString('password', json_encode($attempt->getAttributes()));
    }

    public function test_a_private_address_is_stored_without_a_location_lookup(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $attempt = LoginAttempt::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('127.0.0.1', $attempt->ip_address);
        $this->assertNull($attempt->city_name);
        $this->assertNull($attempt->country_name);
        $this->assertSame('', $attempt->placeLabel());
    }

    public function test_a_failed_password_is_recorded_without_the_password(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $attempt = LoginAttempt::query()->firstOrFail();
        $this->assertSame(LoginAttempt::OUTCOME_FAILED, $attempt->outcome);
        $this->assertSame($user->id, $attempt->user_id);
        $this->assertStringNotContainsString('wrong-password', json_encode($attempt->getAttributes()));
    }

    public function test_an_unknown_account_and_a_lockout_are_recorded(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $unknown = LoginAttempt::query()->where('email', 'nobody@example.com')->firstOrFail();
        $this->assertNull($unknown->user_id);
        $this->assertSame(LoginAttempt::OUTCOME_FAILED, $unknown->outcome);

        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'nope']);
        }

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(
            LoginAttempt::query()->where('user_id', $user->id)->where('outcome', LoginAttempt::OUTCOME_LOCKED)->exists()
        );
    }

    public function test_an_api_sign_in_is_recorded_for_success_and_failure(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->assertSame(1, LoginAttempt::query()->where('guard', 'api')->where('outcome', LoginAttempt::OUTCOME_SUCCESS)->count());
        $this->assertSame(1, LoginAttempt::query()->where('guard', 'api')->where('outcome', LoginAttempt::OUTCOME_FAILED)->count());
    }

    public function test_login_history_is_on_the_super_administrator_dashboard_and_hidden_from_other_staff(): void
    {
        $admin = User::factory()->staff()->create();
        $admin->assignRole(Role::findOrCreate(AccessRoles::SUPER_ADMINISTRATOR, 'web'));
        $admin->givePermissionTo([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::AUDIT_VIEW,
            AccessPermissions::LOGINS_VIEW,
        ]);

        $officer = User::factory()->staff()->create();
        $officer->givePermissionTo(AccessPermissions::ADMIN_DASHBOARD);

        LoginAttempt::query()->forceCreate([
            'email' => 'investor@example.com',
            'outcome' => LoginAttempt::OUTCOME_SUCCESS,
            'guard' => 'web',
            'ip_address' => '8.8.8.8',
            'city_name' => 'Accra',
            'country_name' => 'Ghana',
        ]);

        $this->actingAs($officer)->get(route('admin.logins.index'))->assertForbidden();
        $this->actingAs($officer)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Login history');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Audit log')
            ->assertSee('Login history')
            ->assertSee('investor@example.com');

        $this->actingAs($admin)->get(route('admin.logins.index'))
            ->assertOk()
            ->assertSee('investor@example.com')
            ->assertSee('8.8.8.8')
            ->assertSee('Accra, Ghana')
            ->assertSee('Signed in')
            ->assertDontSee('password');
    }
}
