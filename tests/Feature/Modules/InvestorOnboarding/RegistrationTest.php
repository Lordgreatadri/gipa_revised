<?php

namespace Tests\Feature\Modules\InvestorOnboarding;

use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Infrastructure\Notifications\VerifyEmailAddress;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorRegistered;
use App\Modules\InvestorOnboarding\Domain\InvestorAuditActions;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = $this->makeCountry();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ama Mensah',
            'email' => 'Ama.Mensah@example.com',
            'phone' => '+233 20 123 4567',
            'country' => $this->country->uuid,
            'password' => 'Str0ng-passw0rd!',
            'password_confirmation' => 'Str0ng-passw0rd!',
            'terms' => '1',
        ], $overrides);
    }

    public function test_registration_page_renders_for_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your investor account')
            ->assertSee($this->country->name)
            ->assertSee($this->country->code)
            ->assertSee('ghana_flag.png', false);
    }

    public function test_a_visitor_can_register_as_an_investor_and_is_sent_to_email_verification(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), $this->payload());

        $response->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'ama.mensah@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserType::Investor, $user->user_type);
        $this->assertSame('+233201234567', $user->phone, 'phone is normalised to E.164');
        $this->assertTrue($user->country->is($this->country));
        $this->assertNull($user->email_verified_at, 'registration does not verify e-mail');
        $this->assertNull($user->phone_verified_at, 'registration does not verify phone');
        $this->assertTrue(Hash::check('Str0ng-passw0rd!', $user->password));
        $this->assertNotEmpty($user->uuid);

        Notification::assertSentTo($user, VerifyEmailAddress::class);
    }

    public function test_registration_is_audited_without_secrets(): void
    {
        Notification::fake();

        $this->post(route('register.store'), $this->payload());

        $user = User::query()->firstOrFail();
        $record = $this->assertAuditRecorded(InvestorAuditActions::REGISTERED, $user->id);

        $this->assertSame((string) $user->id, (string) $record->causer_id);
        $this->assertSame('UI', $record->source);
        $this->assertNotNull($record->request_id);
        $this->assertNotNull($record->ip_address);
        $this->assertStringNotContainsString('Str0ng-passw0rd!', json_encode($record->properties));
    }

    public function test_registration_dispatches_the_domain_event(): void
    {
        Event::fake([InvestorRegistered::class]);

        $this->post(route('register.store'), $this->payload());

        Event::assertDispatched(InvestorRegistered::class);
    }

    public function test_a_registered_investor_cannot_access_the_dashboard_before_verifying(): void
    {
        Notification::fake();

        $this->post(route('register.store'), $this->payload());

        $this->get(route('investor.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_staff_cannot_be_created_through_registration(): void
    {
        Notification::fake();

        $this->post(route('register.store'), $this->payload([
            'user_type' => 'staff',
            'role' => 'Super Administrator',
            'roles' => ['admin'],
        ]));

        $user = User::query()->firstOrFail();

        $this->assertSame(UserType::Investor, $user->user_type);
        $this->assertTrue($user->hasRole(AccessRoles::INVESTOR));
        $this->assertFalse($user->hasRole(AccessRoles::SUPER_ADMINISTRATOR));
    }

    public function test_there_is_no_staff_registration_route(): void
    {
        $this->get('/staff/register')->assertNotFound();
        $this->get('/admin/register')->assertNotFound();
        $this->post('/staff/register', $this->payload())->assertNotFound();
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), []);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'phone', 'country', 'password', 'terms']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'ama.mensah@example.com']);

        $this->post(route('register.store'), $this->payload())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_phone_is_rejected(): void
    {
        User::factory()->create(['phone' => '+233201234567']);

        $this->post(route('register.store'), $this->payload(['email' => 'other@example.com']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_phone_must_be_in_international_format(): void
    {
        $this->post(route('register.store'), $this->payload(['phone' => '0201234567']))
            ->assertSessionHasErrors('phone');

        $this->post(route('register.store'), $this->payload(['phone' => '+0201234567']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_country_must_be_an_existing_country_uuid(): void
    {
        $this->post(route('register.store'), $this->payload(['country' => (string) $this->country->id]))
            ->assertSessionHasErrors('country');

        $this->post(route('register.store'), $this->payload(['country' => '00000000-0000-4000-8000-000000000000']))
            ->assertSessionHasErrors('country');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->post(route('register.store'), $this->payload(['password_confirmation' => 'different']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_authenticated_users_cannot_reach_registration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('register'))->assertRedirect();
        $this->actingAs($user)->post(route('register.store'), $this->payload(['email' => 'x@example.com', 'phone' => '+233209999999']))->assertRedirect();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_failure_leaves_no_partial_records(): void
    {
        $this->post(route('register.store'), $this->payload(['terms' => '0']))
            ->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('users', 0);
        $this->assertAuditNotRecorded(InvestorAuditActions::REGISTERED);
    }
}
