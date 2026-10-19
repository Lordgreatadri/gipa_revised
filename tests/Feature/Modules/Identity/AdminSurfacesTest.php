<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSurfacesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (AccessRoles::defaults() as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }

        foreach (AccessRoles::defaults() as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }
    }

    public function test_the_opportunity_overview_exposes_a_quarterly_regional_chart(): void
    {
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);

        $this->actingAs($officer)->get(route('admin.opportunities.index'))
            ->assertOk()
            ->assertSee('QTR 1')
            ->assertSee('QTR 4')
            ->assertSee('chart-regional', false)
            ->assertSee('Workflow mix');
    }

    public function test_the_staff_dashboard_shows_the_portfolio_and_lifecycle(): void
    {
        $admin = User::factory()->staff()->create();
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Staff accounts')
            ->assertSee('Lifecycle snapshot')
            ->assertSee('Workflow mix')
            ->assertSee('Regional distribution')
            ->assertSee('Audit log');
    }

    public function test_geography_and_catalogue_have_separate_pages(): void
    {
        $manager = User::factory()->staff()->create();
        $manager->assignRole(AccessRoles::CONTENT_MANAGER);
        Region::query()->create(['code' => 'AA', 'name' => 'Greater Accra', 'capital' => 'Accra']);

        $this->actingAs($manager)->get(route('admin.regions.index'))->assertOk()->assertSee('Greater Accra');
        $this->get(route('admin.districts.index'))->assertOk()->assertSee('Districts by region');
        $this->get(route('admin.districts.register'))->assertOk()->assertSee('District records');
        $this->get(route('admin.sectors.index'))->assertOk()->assertSee('View records');
        $this->get(route('admin.sub-sectors.index'))->assertOk();
        $this->get(route('admin.types.index'))->assertOk();
        $this->get(route('admin.catalogue.index'))->assertRedirect(route('admin.sectors.index'));
    }

    public function test_audit_log_can_be_exported_by_a_reviewer(): void
    {
        $reviewer = User::factory()->staff()->create();
        $reviewer->assignRole(AccessRoles::REVIEWER);
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);

        $this->actingAs($officer)->get(route('admin.audit'))->assertForbidden();

        $this->actingAs($reviewer)->get(route('admin.audit'))->assertOk()->assertSee('Audit log');
        $export = $this->get(route('admin.audit.export', ['format' => 'csv']));
        $export->assertOk();
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('content-type'));
        $this->assertStringContainsString('Action', $export->streamedContent());

        $this->assertTrue(AuditRecord::query()->where('event', 'audit.exported')->exists());
    }

    public function test_sms_settings_are_limited_to_the_super_administrator_and_hide_the_key(): void
    {
        $admin = User::factory()->staff()->create();
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);
        $manager = User::factory()->staff()->create();
        $manager->assignRole(AccessRoles::CONTENT_MANAGER);

        $this->actingAs($manager)->get(route('admin.sms.index'))->assertForbidden();

        $this->actingAs($admin)->put(route('admin.sms.update', 'frog'), [
            'sender_id' => 'GIPA',
            'priority' => 20,
            'is_active' => '0',
            'credentials' => [
                'username' => 'gateway-user',
                'api_key' => 'secret-key-value',
            ],
        ])->assertRedirect(route('admin.sms.index'));

        $this->get(route('admin.sms.index'))
            ->assertOk()
            ->assertSee('Wigal Frog')
            ->assertSee('Twilio')
            ->assertSee('A secret is stored.')
            ->assertDontSee('secret-key-value');

        $record = AuditRecord::query()->where('event', 'sms.settings_updated')->first();
        $this->assertNotNull($record);
        $this->assertStringNotContainsString('secret-key-value', json_encode($record->properties));
    }

    public function test_an_active_gateway_fails_over_when_the_first_provider_rejects_the_message(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://frog.test/*' => \Illuminate\Support\Facades\Http::response(['status' => 'REJECTED', 'message' => 'declined'], 422),
        ]);
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        $admin = User::factory()->staff()->create();
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);

        $this->actingAs($admin)->put(route('admin.sms.update', 'frog'), [
            'sender_id' => 'GIPA',
            'priority' => 10,
            'is_active' => '1',
            'credentials' => [
                'username' => 'gateway-user',
                'api_key' => 'secret-key-value',
                'base_url' => 'https://frog.test/api',
            ],
        ])->assertRedirect();

        $this->put(route('admin.sms.update', 'log'), [
            'sender_id' => 'GIPA',
            'priority' => 20,
            'is_active' => '1',
        ])->assertRedirect();

        $message = app(\App\Modules\Shared\Infrastructure\Sms\SmsGateway::class)
            ->queue('0244000000', 'A notification that should fail over to the log gateway.', 'contact_reply');

        $this->assertNotNull($message);
        $message->refresh();
        $this->assertSame('sent', $message->status);
        $this->assertSame('log', $message->provider);
        $this->assertSame(['frog', 'log'], collect($message->attempts)->pluck('provider')->all());
    }
}
