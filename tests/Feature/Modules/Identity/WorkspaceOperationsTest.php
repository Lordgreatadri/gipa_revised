<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Geography\Application\GhanaSamplePoint;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\PublicPortal\Domain\Models\ContactUs;
use App\Modules\Shared\Domain\Models\InAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkspaceOperationsTest extends TestCase
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

    public function test_sample_points_stay_inside_ghana_for_every_region(): void
    {
        foreach (['AH', 'AS', 'EP', 'TV', 'OT', 'AA', 'UW'] as $code) {
            [$latitude, $longitude] = GhanaSamplePoint::near($code, 120);
            $this->assertGreaterThanOrEqual(4.74, $latitude);
            $this->assertLessThanOrEqual(11.0, $latitude);
            $this->assertGreaterThanOrEqual(-3.25, $longitude);
            $this->assertLessThanOrEqual(1.05, $longitude);
        }

        [, $eastern] = GhanaSamplePoint::near('EP', 120);
        $this->assertLessThan(0.2, $eastern);
    }

    public function test_a_reviewer_can_reply_to_a_contact_enquiry(): void
    {
        Notification::fake();
        $reviewer = User::factory()->staff()->create();
        $reviewer->assignRole(AccessRoles::SUPER_ADMINISTRATOR);
        $enquiry = ContactUs::query()->create([
            'reference' => 'CON-TEST-0001',
            'name' => 'Ama Mensah',
            'email' => 'ama@example.com',
            'phone_number' => '0241234567',
            'subject' => 'Registration',
            'message' => 'How do I finish verification?',
            'status' => ContactUs::STATUS_NEW,
        ]);

        $this->actingAs($reviewer)->post(route('admin.contact.reply', $enquiry), [
            'body' => 'Please complete the phone code we sent to your handset.',
        ])->assertRedirect();

        $this->assertSame(ContactUs::STATUS_RESPONDED, $enquiry->fresh()->status);
        $this->assertDatabaseHas('contact_replies', ['contact_us_id' => $enquiry->id]);
    }

    public function test_staff_can_export_the_directory_as_csv(): void
    {
        $admin = User::factory()->staff()->create(['name' => 'Export Admin']);
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);

        $response = $this->actingAs($admin)->get(route('admin.users.export', ['format' => 'csv']));
        $response->assertOk();
        $this->assertStringContainsString('Export Admin', $response->streamedContent());
    }

    public function test_an_in_app_notification_can_be_opened(): void
    {
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::INVESTOR);
        $this->giveBusinessProfile($investor);
        $notice = new InAppNotification;
        $notice->forceFill([
            'title' => 'Profile approved',
            'body' => 'You can now inquire.',
            'href' => route('investor.opportunities'),
        ]);
        $notice->user()->associate($investor);
        $notice->save();

        $this->actingAs($investor)->get(route('investor.notifications'))->assertOk()->assertSee('Profile approved');
        $this->actingAs($investor)->post(route('investor.notifications.read', $notice))->assertRedirect(route('investor.opportunities'));
        $this->assertNotNull($notice->fresh()->read_at);
    }
}
