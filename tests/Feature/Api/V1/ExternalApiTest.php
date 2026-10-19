<?php

namespace Tests\Feature\Api\V1;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExternalApiTest extends TestCase
{
    use RefreshDatabase;

    private District $home;

    protected function setUp(): void
    {
        parent::setUp();

        $region = Region::query()->create(['code' => 'AA', 'name' => 'Greater Accra', 'capital' => 'Accra']);
        $this->home = District::query()->create(['region_id' => $region->id, 'code' => 'GH-AA-001', 'name' => 'Ablekuma Central']);

        foreach (AccessRoles::defaults() as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }

        foreach (AccessRoles::defaults() as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }
    }

    public function test_a_verified_investor_reads_only_active_opportunities_without_contact_details(): void
    {
        $investor = $this->investor();
        $this->approveProfile($investor);
        $active = $this->activeOpportunity();
        $draft = $this->draftOpportunity('Unpublished draft record');

        $token = $this->tokenFor($investor);

        $this->getJson('/api/v1/opportunities')->assertUnauthorized();

        $list = $this->withToken($token)->getJson('/api/v1/opportunities?sort=amount_desc');
        $list->assertOk()
            ->assertJsonPath('data.0.title', 'Coastal cold store')
            ->assertJsonPath('meta.per_page', 12)
            ->assertJsonMissing(['title' => 'Unpublished draft record'])
            ->assertJsonMissing(['contact_email' => 'ama@example.test']);

        $this->withToken($token)->getJson('/api/v1/opportunities/'.$draft->uuid)->assertNotFound();

        $this->withToken($token)->getJson('/api/v1/opportunities/'.$active->uuid)
            ->assertOk()
            ->assertJsonPath('data.title', 'Coastal cold store')
            ->assertJsonMissing(['contact_email' => 'ama@example.test']);

        $this->withToken($token)->getJson('/api/v1/opportunities?sort=workflow_state')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Coastal cold store');
    }

    public function test_staff_and_unverified_investors_cannot_read_the_register(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->assignRole(AccessRoles::SUPER_ADMINISTRATOR);
        $unverified = User::factory()->phoneUnverified()->create();
        $unverified->assignRole(AccessRoles::INVESTOR);

        $this->withToken($this->tokenFor($staff))
            ->getJson('/api/v1/opportunities')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');

        $this->withToken($this->tokenFor($unverified))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.phone_verified', false);

        $this->withToken($this->tokenFor($unverified))
            ->getJson('/api/v1/opportunities')
            ->assertForbidden()
            ->assertJsonPath('error.message', 'Investor verification is incomplete.');

        $verified = $this->investor();
        $this->withToken($this->tokenFor($verified))
            ->getJson('/api/v1/opportunities')
            ->assertForbidden()
            ->assertJsonPath('error.message', 'Create a business profile before using investor resources.');
    }

    public function test_refresh_tokens_rotate_and_can_be_revoked(): void
    {
        $investor = $this->investor();
        $issued = $this->postJson('/api/v1/auth/token', [
            'email' => $investor->email,
            'password' => 'password',
        ])->assertOk()->json('data');

        $this->postJson('/api/v1/auth/token', [
            'email' => $investor->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertJsonPath('error.code', 'unauthenticated');

        $rotated = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $issued['refresh_token'],
        ])->assertOk()->json('data');

        $this->assertNotSame($issued['refresh_token'], $rotated['refresh_token']);

        $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $issued['refresh_token'],
        ])->assertUnauthorized();

        $this->postJson('/api/v1/auth/revoke', [
            'refresh_token' => $rotated['refresh_token'],
        ])->assertOk()->assertJsonPath('data.revoked', true);

        $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $rotated['refresh_token'],
        ])->assertUnauthorized();
    }

    public function test_an_approved_investor_can_inquire_through_the_api(): void
    {
        $investor = $this->investor();
        $this->approveProfile($investor);
        $active = $this->activeOpportunity();
        $token = $this->tokenFor($investor);

        $this->withToken($token)->postJson('/api/v1/opportunities/'.$active->uuid.'/inquiries', [
            'message' => 'Too short',
            'consent' => true,
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

        $this->withToken($token)->postJson('/api/v1/opportunities/'.$active->uuid.'/inquiries', [
            'message' => 'We would like the feasibility note and a site visit.',
            'consent' => true,
        ])->assertCreated()->assertJsonPath('data.kind', 'inquiry')->assertJsonPath('data.state', 'submitted');

        $this->withToken($token)->getJson('/api/v1/engagements')
            ->assertOk()
            ->assertJsonPath('data.0.kind', 'inquiry');
    }

    public function test_reference_data_and_the_openapi_contract_are_public(): void
    {
        $this->getJson('/api/v1/regions')->assertOk()->assertJsonPath('data.0.code', 'AA');
        $this->getJson('/api/v1/sectors')->assertOk()->assertJsonFragment(['code' => 'AGR']);

        $spec = $this->getJson('/api/v1/openapi.json')->assertOk()->json();

        $this->assertSame('3.0.3', $spec['openapi']);
        $this->assertArrayHasKey('/api/v1/opportunities', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/token', $spec['paths']);
        $this->assertSame('bearer', $spec['components']['securitySchemes']['bearerAuth']['scheme']);

        $this->get(route('api.documentation', ['q' => 'refresh']))
            ->assertOk()
            ->assertSee('Refresh an access token')
            ->assertSee('cURL')
            ->assertSee('PHP')
            ->assertSee('Node.js')
            ->assertSee('curl_init')
            ->assertSee('await fetch')
            ->assertSee('401')
            ->assertSee('422')
            ->assertSee('opaque string')
            ->assertDontSee('Service status')
            ->assertDontSee('openapi.json');

        $this->get(route('platform.guide'))
            ->assertOk()
            ->assertSee('Create a business profile')
            ->assertDontSee('GIPA Assistant');
    }

    public function test_reporting_is_a_placeholder_in_the_staff_workspace(): void
    {
        $admin = User::factory()->staff()->create();
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);

        $this->actingAs($admin)->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Reporting')
            ->assertSee('Placeholder')
            ->assertSee('Open the dashboard');
    }

    private function investor(): User
    {
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::INVESTOR);

        return $investor;
    }

    private function tokenFor(User $user): string
    {
        return $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.access_token');
    }

    private function approveProfile(User $investor): void
    {
        $profile = new InvestorProfile;
        $profile->forceFill([
            'organization_name' => $investor->name,
            'business_email' => $investor->email,
            'contact_phone' => $investor->phone,
            'profile_type' => ProfileType::Individual,
            'nationality' => 'Ghana',
            'country_code' => 'GH',
            'currency' => 'GHS',
            'preferred_language' => 'en',
            'onboarding_state' => ProfileOnboardingState::Approved,
            'status' => ProfileStatus::Active,
        ]);
        $profile->user()->associate($investor);
        $profile->save();
    }

    private function activeOpportunity(): Opportunity
    {
        $this->draftOpportunity('Coastal cold store');
        $opportunity = Opportunity::query()->where('title', 'Coastal cold store')->firstOrFail();
        $officer = User::query()->where('user_type', 'staff')->firstOrFail();
        $reviewer = User::factory()->staff()->create();
        $reviewer->assignRole(AccessRoles::REVIEWER);
        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'submit']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'approve']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'activate']);

        return $opportunity->fresh();
    }

    private function draftOpportunity(string $title): Opportunity
    {
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);
        $officer->districts()->attach($this->home);

        $this->actingAs($officer)->post(route('admin.opportunities.store'), [
            'title' => $title,
            'sector_id' => Sector::query()->where('code', 'AGR')->firstOrFail()->uuid,
            'sub_sector_id' => SubSector::query()->where('code', 'AGR-PROC')->firstOrFail()->uuid,
            'enterprise_type_id' => EnterpriseType::query()->where('code', 'PLL')->firstOrFail()->uuid,
            'description' => 'A cold storage facility for landed fish.',
            'district_id' => $this->home->uuid,
            'location_description' => 'Near the landing beach',
            'latitude' => '5.603700',
            'longitude' => '-0.187000',
            'investment_amount' => '1500000',
            'currency' => 'GHS',
            'investment_structure' => 'local_equity',
            'contact_name' => 'Ama Mensah',
            'contact_phone' => '+233200000111',
            'contact_email' => 'ama@example.test',
        ])->assertRedirect();

        return Opportunity::query()->where('title', $title)->firstOrFail();
    }
}
