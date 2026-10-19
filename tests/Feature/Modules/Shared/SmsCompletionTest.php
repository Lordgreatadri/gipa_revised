<?php

namespace Tests\Feature\Modules\Shared;

use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Shared\Domain\Models\InAppNotification;
use App\Modules\Shared\Infrastructure\Sms\BalanceWatch;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\SmsCampaign;
use App\Modules\Shared\Infrastructure\Sms\SmsCampaignRecipient;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SmsCompletionTest extends TestCase
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

    public function test_frog_posts_json_to_the_documented_send_url(): void
    {
        Http::fake([
            'https://frog.test/api/v3/sms/send' => Http::response([
                'status' => 'ACCEPTD',
                'message' => 'Message Accepted For Processing',
            ], 200),
        ]);
        Http::preventStrayRequests();

        $admin = $this->administrator();
        $this->actingAs($admin)->put(route('admin.sms.update', 'frog'), [
            'sender_id' => 'GIPA',
            'priority' => 10,
            'is_active' => '1',
            'credentials' => [
                'username' => 'gateway-user',
                'api_key' => 'secret-key-value',
                'base_url' => 'https://frog.test/api/v3',
            ],
        ])->assertRedirect();

        $message = app(\App\Modules\Shared\Infrastructure\Sms\SmsGateway::class)
            ->sendNow('0244000000', 'A local gateway check.', 'sms.test', 'frog');

        $this->assertNotNull($message);
        $this->assertSame(SmsMessage::SENT, $message->status);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://frog.test/api/v3/sms/send'
                && $request->method() === 'POST'
                && $request->hasHeader('API-KEY', 'secret-key-value')
                && $request['destinations'][0]['destination'] === '233244000000'
                && $request['smstype'] === 'text';
        });
    }

    public function test_a_low_bundle_with_remaining_credit_alerts_once_per_day(): void
    {
        $admin = $this->administrator();
        $config = $this->balanceConfig('frog', credit: 80, units: 4, creditThreshold: 20, bundleThreshold: 10);

        $watch = app(BalanceWatch::class);
        $this->assertSame('credit_fallback', $watch->level($config, ProviderBalance::fromArray('frog', $config->balance_snapshot)));

        $watch->notifyIfNeeded($config->fresh());
        $watch->notifyIfNeeded($config->fresh());

        $this->assertSame(1, InAppNotification::query()->where('user_id', $admin->id)->count());
        $this->assertSame('credit_fallback', $config->fresh()->balance_alert_level);

        $config->forceFill(['balance_alerted_at' => now()->subDays(2)])->save();
        $watch->notifyIfNeeded($config->fresh());
        $this->assertSame(2, InAppNotification::query()->where('user_id', $admin->id)->count());
    }

    public function test_exhausted_bundles_and_low_credit_ask_for_a_top_up(): void
    {
        $admin = $this->administrator();
        $frog = $this->balanceConfig('frog', credit: 5, units: 0, creditThreshold: 20, bundleThreshold: 10);
        $twilio = $this->balanceConfig('twilio', credit: 5, units: null, creditThreshold: 20, bundleThreshold: null);

        $watch = app(BalanceWatch::class);
        $this->assertSame('top_up', $watch->level($frog, ProviderBalance::fromArray('frog', $frog->balance_snapshot)));
        $this->assertSame('top_up', $watch->level($twilio, ProviderBalance::fromArray('twilio', $twilio->balance_snapshot)));

        $watch->notifyIfNeeded($frog->fresh());
        $notice = InAppNotification::query()->where('user_id', $admin->id)->first();
        $this->assertNotNull($notice);
        $this->assertStringContainsString('needs a top-up', $notice->title);
    }

    public function test_the_daily_balance_watch_reads_frog_bundles_and_cash(): void
    {
        Http::fake([
            'https://frog.test/api/v3/balance' => Http::response([
                'data' => ['cashbalance' => 4.5, 'bundles' => ['SMS' => 2]],
            ], 200),
        ]);
        Http::preventStrayRequests();

        $admin = $this->administrator();
        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => 'frog',
            'is_active' => true,
            'credentials' => [
                'username' => 'gateway-user',
                'api_key' => 'secret-key-value',
                'base_url' => 'https://frog.test/api/v3',
            ],
            'credit_threshold' => 10,
            'bundle_threshold' => 5,
        ])->save();

        $this->artisan('sms:watch-balances')->assertSuccessful();

        $config->refresh();
        $this->assertSame(2, $config->balance_snapshot['sms_units']);
        $this->assertSame('top_up', $config->balance_alert_level);
        $this->assertSame(1, InAppNotification::query()->where('user_id', $admin->id)->count());
    }

    public function test_a_personal_campaign_reaches_active_investors_only(): void
    {
        $admin = $this->administrator();
        $this->actingAs($admin);
        $this->activateLog();

        $approved = User::factory()->create(['name' => 'Ama Mensah']);
        $this->profile($approved, ProfileStatus::Active, ProfileOnboardingState::Approved, 'Ama Farms');
        $pending = User::factory()->create(['name' => 'Kojo Pending']);
        $this->profile($pending, ProfileStatus::Active, ProfileOnboardingState::PendingApproval, 'Pending Co');

        $this->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Site notice',
            'audience' => 'active_investors',
            'placeholder' => 'name',
            'body' => 'Hello [[NAME]], the register is open.',
        ])->assertRedirect();

        $campaign = SmsCampaign::query()->firstOrFail();
        $this->assertSame(SmsCampaign::COMPLETED, $campaign->status);
        $this->assertSame(1, $campaign->sent_count);

        $recipient = SmsCampaignRecipient::query()->firstOrFail();
        $this->assertSame('Ama Mensah', $recipient->name);
        $message = SmsMessage::query()->findOrFail($recipient->sms_message_id);
        $this->assertSame('Hello Ama Mensah, the register is open.', $message->body);
        $this->assertSame($campaign->id, $message->campaign_id);

        $this->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Business notice',
            'audience' => 'active_investors',
            'placeholder' => 'business_name',
            'body' => 'Hello [[BUSINESS_NAME]], welcome.',
        ])->assertRedirect();

        $this->assertSame('Hello Ama Farms, welcome.', SmsMessage::query()->where('body', 'like', 'Hello Ama Farms%')->value('body'));
    }

    public function test_a_directory_message_cannot_use_both_placeholders(): void
    {
        $this->actingAs($this->administrator())->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Mixed notice',
            'audience' => 'active_investors',
            'placeholder' => 'name',
            'body' => 'Hello [[NAME]] / [[BUSINESS_NAME]]',
        ])->assertSessionHasErrors('body');

        $this->assertSame(0, SmsCampaign::query()->count());
    }

    public function test_a_rendered_message_over_520_characters_is_skipped(): void
    {
        $this->actingAs($this->administrator());
        $this->activateLog();
        $investor = User::factory()->create(['name' => str_repeat('N', 30)]);
        $this->profile($investor, ProfileStatus::Active, ProfileOnboardingState::Approved, 'Farms');

        $this->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Long personal',
            'audience' => 'active_investors',
            'placeholder' => 'name',
            'body' => str_repeat('x', 512).'[[NAME]]',
        ])->assertRedirect();

        $recipient = SmsCampaignRecipient::query()->firstOrFail();
        $this->assertSame(SmsCampaignRecipient::SKIPPED, $recipient->status);
        $this->assertStringContainsString('520', (string) $recipient->detail);
        $this->assertSame(SmsCampaign::FAILED, SmsCampaign::query()->firstOrFail()->status);
    }

    public function test_a_content_manager_cannot_message_all_staff(): void
    {
        $manager = User::factory()->staff()->create();
        $manager->assignRole(AccessRoles::CONTENT_MANAGER);

        $this->actingAs($manager)->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Staff note',
            'audience' => 'all_staff',
            'placeholder' => 'name',
            'body' => 'Hello [[NAME]], please check the register.',
        ])->assertForbidden();
    }

    public function test_a_campaign_body_cannot_exceed_520_characters(): void
    {
        $this->actingAs($this->administrator())->post(route('admin.sms.campaigns.store'), [
            'batch' => 'numbers',
            'name' => 'Too long',
            'body' => str_repeat('a', 521),
            'numbers' => '0244000001',
        ])->assertSessionHasErrors('body');
    }

    public function test_semicolon_numbers_and_a_csv_file_become_recipients(): void
    {
        $this->actingAs($this->administrator());
        $this->activateLog();

        $this->post(route('admin.sms.campaigns.store'), [
            'batch' => 'numbers',
            'name' => 'Manual list',
            'body' => 'Register reminder.',
            'numbers' => '0244000001; 0244000002',
        ])->assertRedirect();

        $this->assertSame(2, SmsCampaign::query()->where('audience', 'numbers')->firstOrFail()->sent_count);

        $this->post(route('admin.sms.campaigns.store'), [
            'batch' => 'file',
            'name' => 'File list',
            'body' => 'Register notice.',
            'contacts' => UploadedFile::fake()->createWithContent(
                'contacts.csv',
                "phone,name,business_name\n0244000099,Kojo,Kojo Ltd\n",
            ),
        ])->assertRedirect();

        $message = SmsMessage::query()->where('body', 'Register notice.')->first();
        $this->assertNotNull($message);
        $this->assertSame('233244000099', $message->recipient);
    }

    public function test_overdue_certificate_renewals_have_no_records_yet(): void
    {
        $this->actingAs($this->administrator())->post(route('admin.sms.campaigns.store'), [
            'batch' => 'directory',
            'name' => 'Renewals',
            'audience' => 'overdue_certificates',
            'placeholder' => 'name',
            'body' => 'Hello [[NAME]], your certificate renewal is due.',
        ])->assertSessionHasErrors('audience');

        $this->assertSame(0, SmsCampaign::query()->count());
    }

    public function test_messages_have_their_own_page_and_only_undelivered_ones_can_be_resent(): void
    {
        $admin = $this->administrator();
        $officer = User::factory()->staff()->create();
        $officer->assignRole(AccessRoles::DISTRICT_OFFICER);
        $manager = User::factory()->staff()->create();
        $manager->assignRole(AccessRoles::CONTENT_MANAGER);

        $this->actingAs($officer)->get(route('admin.sms.messages'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.sms.messages'))->assertOk()->assertSee('SMS messages');
        $this->actingAs($manager)->get(route('admin.sms.index'))->assertForbidden();

        $this->actingAs($admin);
        $this->activateLog();

        $failed = new SmsMessage;
        $failed->forceFill([
            'reference' => 'SMS-20261001-FAILED01',
            'recipient' => '233244000001',
            'body' => 'Please try this notice again.',
            'context' => 'sms.test',
            'provider' => 'frog',
            'status' => SmsMessage::FAILED,
            'error' => 'REJECTED',
        ])->save();

        $sent = new SmsMessage;
        $sent->forceFill([
            'reference' => 'SMS-20261001-SENT0001',
            'recipient' => '233244000002',
            'body' => 'Already accepted.',
            'context' => 'sms.test',
            'provider' => 'log',
            'status' => SmsMessage::SENT,
        ])->save();

        $this->get(route('admin.sms.messages.show', $failed))
            ->assertOk()
            ->assertSee('Please try this notice again.')
            ->assertSee('Send again');

        $waiting = new SmsMessage;
        $waiting->forceFill([
            'reference' => 'SMS-20261001-WAIT0001',
            'recipient' => '233244000003',
            'body' => 'Still waiting to leave.',
            'context' => 'sms.test',
            'provider' => 'log',
            'status' => SmsMessage::QUEUED,
            'queued_at' => now()->subHours(2),
        ])->save();

        $this->get(route('admin.sms.messages.show', $waiting))
            ->assertOk()
            ->assertSee('Send again')
            ->assertSee('waiting for more than an hour');

        $this->post(route('admin.sms.messages.resend', $sent))->assertSessionHasErrors('sms');
        $this->post(route('admin.sms.messages.resend', $waiting))->assertRedirect();
        $this->post(route('admin.sms.messages.resend', $failed))->assertRedirect();

        $copy = SmsMessage::query()->where('resent_from_id', $failed->id)->first();
        $this->assertNotNull($copy);
        $this->assertSame(SmsMessage::SENT, $copy->status);
        $this->assertSame('sms.resend', $copy->context);
    }

    private function administrator(): User
    {
        $admin = User::factory()->staff()->create();
        $admin->assignRole(AccessRoles::SUPER_ADMINISTRATOR);

        return $admin;
    }

    private function activateLog(): void
    {
        $this->put(route('admin.sms.update', 'log'), [
            'sender_id' => 'GIPA',
            'priority' => 10,
            'is_active' => '1',
        ])->assertRedirect();
    }

    private function profile(User $investor, ProfileStatus $status, ProfileOnboardingState $state, string $business): void
    {
        $profile = new InvestorProfile;
        $profile->forceFill([
            'organization_name' => $business,
            'business_email' => $investor->email,
            'contact_phone' => $investor->phone,
            'profile_type' => ProfileType::Individual,
            'nationality' => 'Ghana',
            'country_code' => 'GH',
            'currency' => 'GHS',
            'preferred_language' => 'en',
            'onboarding_state' => $state,
            'status' => $status,
        ]);
        $profile->user()->associate($investor);
        $profile->save();
    }

    private function balanceConfig(string $provider, float $credit, ?int $units, float $creditThreshold, ?int $bundleThreshold): SmsProviderConfig
    {
        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => $provider,
            'is_active' => true,
            'credentials' => $provider === 'twilio'
                ? ['account_sid' => 'AC123', 'auth_token' => 'token-value']
                : ['username' => 'gateway-user', 'api_key' => 'secret-key-value'],
            'credit_threshold' => $creditThreshold,
            'bundle_threshold' => $bundleThreshold,
            'balance_snapshot' => [
                'available' => true,
                'credit_balance' => $credit,
                'currency' => 'GHS',
                'sms_units' => $units,
                'bundles' => [],
                'message' => 'Balance retrieved.',
            ],
            'balance_checked_at' => now(),
        ])->save();

        return $config;
    }
}
