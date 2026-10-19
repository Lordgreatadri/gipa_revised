<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in');
    }

    public function test_an_investor_can_log_in_and_is_sent_to_the_investor_area(): void
    {
        $user = User::factory()->create(['email' => 'investor@example.com']);

        $response = $this->post(route('login.store'), [
            'email' => 'Investor@Example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('investor.profile'));
        $this->assertAuthenticatedAs($user);

        $record = $this->assertAuditRecorded(IdentityAuditActions::LOGIN_SUCCEEDED, $user->id);
        $this->assertSame('UI', $record->source);
    }

    public function test_an_unverified_investor_can_log_in_to_continue_onboarding(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('investor.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('investor.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_staff_without_an_admin_role_stay_on_the_public_home_page(): void
    {
        $staff = User::factory()->staff()->create();

        $this->post(route('login.store'), ['email' => $staff->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
    }

    public function test_wrong_password_is_rejected_and_audited_without_the_password(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();

        $record = $this->assertAuditRecorded(IdentityAuditActions::LOGIN_FAILED);
        $json = json_encode($record->properties);
        $this->assertStringNotContainsString('wrong-password', $json);
        $this->assertStringContainsString($user->email, $json);
    }

    public function test_unknown_email_is_rejected_the_same_way(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'nope']);
        }

        $response = $this->from(route('login'))->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertAuditRecorded(IdentityAuditActions::LOGIN_LOCKED_OUT);
    }

    public function test_input_is_validated(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $this->post(route('login.store'), ['email' => 'not-an-email', 'password' => 'x'])->assertSessionHasErrors('email');
    }

    public function test_logout_invalidates_the_session_and_is_audited(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertAuditRecorded(IdentityAuditActions::LOGOUT, $user->id);
    }

    public function test_logout_requires_authentication_and_post(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get('/logout')->assertStatus(405);
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('login'))->assertRedirect();
    }

    public function test_the_login_response_never_exposes_the_password_hash(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }
}
