<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Client-INV-04 gate on the investor experience.
 */
class VerifiedInvestorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('investor.dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_registered_only_investor_is_sent_to_email_verification(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)->get(route('investor.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_an_email_verified_investor_is_sent_to_phone_verification(): void
    {
        $user = User::factory()->phoneUnverified()->create();

        $this->actingAs($user)->get(route('investor.dashboard'))->assertRedirect(route('phone.verification.notice'));
    }

    public function test_a_phone_verified_but_email_unverified_investor_is_sent_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('investor.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_a_fully_verified_investor_must_create_a_business_profile_first(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('investor.dashboard'))->assertRedirect(route('investor.profile'));
        $this->actingAs($user)->get(route('investor.opportunities'))->assertRedirect(route('investor.profile'));
        $this->actingAs($user)->get(route('investor.profile'))
            ->assertOk()
            ->assertSee('Save this profile before the rest of the workspace opens');

        $this->giveBusinessProfile($user);

        $this->actingAs($user->fresh())->get(route('investor.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$user->name)
            ->assertSee('E-mail and phone verified');
    }

    public function test_staff_are_forbidden_from_the_investor_area_even_when_verified(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('investor.dashboard'))->assertForbidden();
    }

    public function test_json_clients_receive_403_instead_of_a_redirect(): void
    {
        $user = User::factory()->phoneUnverified()->create();

        $this->actingAs($user)->getJson(route('investor.dashboard'))->assertForbidden();
    }
}
