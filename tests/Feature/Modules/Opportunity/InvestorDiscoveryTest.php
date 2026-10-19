<?php

namespace Tests\Feature\Modules\Opportunity;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
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

class InvestorDiscoveryTest extends TestCase
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

    public function test_the_map_and_inquiry_are_limited_to_an_active_opportunity(): void
    {
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);
        $officer->districts()->attach($this->home);
        $reviewer = User::factory()->staff()->create();
        $reviewer->assignRole(AccessRoles::REVIEWER);
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::INVESTOR);
        $this->approveProfile($investor);

        $this->actingAs($officer)->post(route('admin.opportunities.store'), $this->payload())->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail();

        $this->actingAs($investor)->get(route('investor.map.opportunities', [
            'minLng' => -1,
            'minLat' => 5,
            'maxLng' => 0,
            'maxLat' => 7,
        ]))->assertOk()->assertJsonMissing(['title' => 'Coastal cold store']);

        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'submit']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'approve']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'activate']);

        $this->actingAs($investor)->get(route('investor.opportunities'))
            ->assertOk()
            ->assertSee('Coastal cold store')
            ->assertSee('Apply filters')
            ->assertDontSee('In progress');

        $this->actingAs($investor)->get(route('investor.map'))->assertOk()->assertSee('investment-map', false);

        $geojson = $this->actingAs($investor)->get(route('investor.map.opportunities', [
            'minLng' => -1,
            'minLat' => 5,
            'maxLng' => 0,
            'maxLat' => 7,
        ]))->assertOk()->json();

        $this->assertSame('Coastal cold store', $geojson['features'][0]['properties']['title']);

        $this->actingAs($investor)->post(route('investor.opportunities.inquire', $opportunity), [
            'message' => 'We would like the feasibility note and a site visit.',
            'consent' => '1',
        ])->assertRedirect();

        $engagement = InvestorEngagement::query()->firstOrFail();

        $this->actingAs($investor)->get(route('investor.engagement'))
            ->assertOk()
            ->assertSee('Inquiry')
            ->assertSee(route('investor.opportunities.show', $opportunity), false);

        $this->actingAs($investor)->get(route('investor.engagement.show', $engagement))
            ->assertOk()
            ->assertSee(route('investor.opportunities.show', $opportunity), false);

        $this->actingAs($reviewer)->get(route('admin.engagement'))
            ->assertOk()
            ->assertSee('Coastal cold store')
            ->assertSee(route('admin.opportunities.show', $opportunity), false);

        $this->actingAs($reviewer)->get(route('admin.engagement.show', $engagement))
            ->assertOk()
            ->assertSee(route('admin.opportunities.show', $opportunity), false);
    }

    public function test_a_district_officer_cannot_manage_the_catalogue(): void
    {
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);

        $this->actingAs($officer)->get(route('admin.catalogue.index'))->assertForbidden();
    }

    public function test_a_content_manager_can_edit_catalogue_records(): void
    {
        $manager = User::factory()->staff()->create();
        $manager->assignRole(AccessRoles::CONTENT_MANAGER);
        $sector = Sector::query()->where('code', 'AGR')->firstOrFail();
        $subSector = SubSector::query()->where('code', 'AGR-PROC')->firstOrFail();
        $type = EnterpriseType::query()->where('code', 'PLL')->firstOrFail();

        $this->actingAs($manager)->get(route('admin.sectors.register'))->assertOk()->assertSee('Edit');

        $this->put(route('admin.catalogue.sectors.update', $sector), [
            'name' => 'Agriculture and fisheries',
            'code' => 'AGR',
            'description' => 'Crops, livestock and fisheries.',
            'is_active' => '1',
            '_record' => $sector->uuid,
        ])->assertRedirect();

        $this->put(route('admin.catalogue.sub-sectors.update', $subSector), [
            'sector_id' => $sector->uuid,
            'name' => 'Fish processing',
            'code' => 'AGR-PROC',
            'is_active' => '1',
            '_record' => $subSector->uuid,
        ])->assertRedirect();

        $this->put(route('admin.catalogue.types.update', $type), [
            'name' => 'Private limited company',
            'code' => 'PLL',
            'is_active' => '1',
            '_record' => $type->uuid,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Agriculture and fisheries', $sector->fresh()->name);
        $this->assertSame('Fish processing', $subSector->fresh()->name);
        $this->assertSame('Private limited company', $type->fresh()->name);
    }

    public function test_an_inquiry_waits_for_an_approved_business_profile(): void
    {
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::INVESTOR);
        $opportunity = $this->activeOpportunity();

        $this->actingAs($investor)->post(route('investor.opportunities.inquire', $opportunity), [
            'message' => 'We would like the feasibility note and a site visit.',
            'consent' => '1',
        ])->assertSessionHasErrors('message');
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
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);
        $officer->districts()->attach($this->home);
        $reviewer = User::factory()->staff()->create();
        $reviewer->assignRole(AccessRoles::REVIEWER);
        $this->actingAs($officer)->post(route('admin.opportunities.store'), $this->payload())->assertRedirect();
        $opportunity = Opportunity::query()->latest('id')->firstOrFail();
        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'submit']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'approve']);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'activate']);

        return $opportunity->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'title' => 'Coastal cold store',
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
        ];
    }
}
