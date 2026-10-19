<?php

namespace Tests\Feature\Modules\Opportunity;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\ProjectStatus;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use App\Modules\Opportunity\Domain\OpportunityAuditActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OpportunityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private District $home;

    private District $other;

    protected function setUp(): void
    {
        parent::setUp();

        $region = Region::query()->create(['code' => 'AA', 'name' => 'Greater Accra', 'capital' => 'Accra']);
        $this->home = District::query()->create(['region_id' => $region->id, 'code' => 'GH-AA-001', 'name' => 'Ablekuma Central']);
        $this->other = District::query()->create(['region_id' => $region->id, 'code' => 'GH-AA-002', 'name' => 'Ablekuma North']);

        foreach (AccessRoles::defaults() as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }

        foreach (AccessRoles::defaults() as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }
    }

    public function test_a_district_officer_records_a_draft_only_in_an_assigned_district(): void
    {
        $officer = $this->staff(AccessRoles::DISTRICT_OFFICER, $this->home);

        $this->actingAs($officer)->post(route('admin.opportunities.store'), $this->payload($this->other))->assertSessionHasErrors('district_id');
        $this->assertSame(0, Opportunity::query()->count());

        $this->actingAs($officer)->post(route('admin.opportunities.store'), $this->payload($this->home))->assertRedirect();

        $opportunity = Opportunity::query()->firstOrFail();
        $this->assertSame(WorkflowState::Draft, $opportunity->workflow_state);
        $this->assertSame(ProjectStatus::NotStarted, $opportunity->project_status);
        $this->assertSame($this->home->id, $opportunity->district_id);
        $this->assertAuditRecorded(OpportunityAuditActions::CREATED, $opportunity->id);
    }

    public function test_another_district_is_hidden_and_approval_is_a_reviewer_action(): void
    {
        $officer = $this->staff(AccessRoles::DISTRICT_OFFICER, $this->home);
        $reviewer = $this->staff(AccessRoles::REVIEWER);
        $opportunity = $this->record($officer);

        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'approve',
        ])->assertForbidden();

        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'submit',
        ])->assertRedirect();

        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'reject',
        ])->assertSessionHasErrors('reason');

        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'reject',
            'reason' => 'The location description is incomplete.',
        ])->assertRedirect();

        $opportunity->refresh();
        $this->assertSame(WorkflowState::Draft, $opportunity->workflow_state);

        $this->actingAs($officer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'submit',
        ]);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'approve',
        ]);
        $this->actingAs($reviewer)->post(route('admin.opportunities.transition', $opportunity), [
            'action' => 'activate',
        ]);

        $opportunity->refresh();
        $this->assertSame(ProjectStatus::InProgress, $opportunity->project_status);

        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::investorRole());
        $this->giveBusinessProfile($investor);

        $this->actingAs($investor)->get(route('investor.opportunities'))
            ->assertOk()
            ->assertSee($opportunity->title)
            ->assertDontSee('Ama Mensah');

        $this->actingAs($investor)->get(route('investor.opportunities.show', $opportunity))
            ->assertOk()
            ->assertDontSee($opportunity->contact_email);

        $hidden = $this->record($this->staff(AccessRoles::CONTENT_MANAGER), $this->other);
        $this->actingAs($officer)->get(route('admin.opportunities.show', $hidden))->assertNotFound();
        $this->actingAs($investor)->get(route('investor.opportunities.show', $hidden))->assertNotFound();
    }

    public function test_a_pending_record_cannot_be_edited(): void
    {
        $manager = $this->staff(AccessRoles::CONTENT_MANAGER);
        $opportunity = $this->record($manager);
        $this->actingAs($manager)->post(route('admin.opportunities.transition', $opportunity), ['action' => 'submit']);

        $this->actingAs($manager)->put(route('admin.opportunities.update', $opportunity), $this->payload($this->home))
            ->assertSessionHasErrors('title');
    }

    public function test_a_sub_sector_must_belong_to_the_selected_sector(): void
    {
        $officer = $this->staff(AccessRoles::DISTRICT_OFFICER, $this->home);
        $payload = $this->payload($this->home);
        $payload['sector_id'] = Sector::query()->where('code', 'HEA')->firstOrFail()->uuid;
        $payload['sub_sector_id'] = SubSector::query()->where('code', 'AGR-CROP')->firstOrFail()->uuid;

        $this->actingAs($officer)->post(route('admin.opportunities.store'), $payload)
            ->assertSessionHasErrors('sub_sector_id');
        $this->assertSame(0, Opportunity::query()->count());
    }

    public function test_a_district_officer_sees_records_they_handled(): void
    {
        $officer = $this->staff(AccessRoles::DISTRICT_OFFICER, $this->home);
        $mine = $this->record($officer);
        $theirs = $this->record($this->staff(AccessRoles::CONTENT_MANAGER), $this->home);
        $theirs->forceFill(['title' => 'Harbour warehouse'])->save();

        $this->actingAs($officer)->get(route('admin.opportunities.register'))
            ->assertOk()
            ->assertSee($mine->title)
            ->assertDontSee($theirs->title);

        $this->actingAs($officer)->get(route('admin.opportunities.show', $theirs))->assertNotFound();
        $this->actingAs($officer)->get(route('admin.opportunities.index'))
            ->assertOk()
            ->assertSee('Opportunities by sector');
    }

    private function staff(string $role, ?District $district = null): User
    {
        $user = User::factory()->staff()->create();
        $user->assignRole($role);

        if ($district !== null) {
            $user->districts()->attach($district);
        }

        return $user;
    }

    private function record(User $actor, ?District $district = null): Opportunity
    {
        $this->actingAs($actor)->post(route('admin.opportunities.store'), $this->payload($district ?? $this->home))->assertRedirect();

        return Opportunity::query()->latest('id')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(District $district): array
    {
        return [
            'title' => 'Coastal cold store',
            'sector_id' => Sector::query()->where('code', 'AGR')->firstOrFail()->uuid,
            'sub_sector_id' => SubSector::query()->where('code', 'AGR-PROC')->firstOrFail()->uuid,
            'enterprise_type_id' => EnterpriseType::query()->where('code', 'PLL')->firstOrFail()->uuid,
            'description' => 'A cold storage facility for landed fish.',
            'objectives' => 'Reduce post-harvest loss.',
            'district_id' => $district->uuid,
            'location_description' => 'Near the landing beach',
            'latitude' => '5.603700',
            'longitude' => '-0.187000',
            'investment_amount' => '1500000',
            'currency' => 'GHS',
            'investment_structure' => 'local_equity',
            'contact_name' => 'Ama Mensah',
            'contact_organization' => 'Coastal Stores',
            'contact_phone' => '+233200000111',
            'contact_email' => 'ama@example.test',
        ];
    }
}
