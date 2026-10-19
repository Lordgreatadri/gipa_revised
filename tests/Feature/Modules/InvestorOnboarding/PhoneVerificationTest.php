<?php

namespace Tests\Feature\Modules\InvestorOnboarding;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\RequestPhoneVerification;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorPhoneVerified;
use App\Modules\InvestorOnboarding\Domain\Exceptions\NotAnInvestor;
use App\Modules\InvestorOnboarding\Domain\InvestorAuditActions;
use App\Modules\InvestorOnboarding\Domain\Models\PhoneVerificationCode;
use App\Modules\InvestorOnboarding\Infrastructure\Notifications\InvestorAccountVerified;
use App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification\ArrayPhoneVerificationSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Monolog\Handler\TestHandler;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    private ArrayPhoneVerificationSender $sender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = $this->app->make(ArrayPhoneVerificationSender::class);
    }

    private function emailVerifiedInvestor(): User
    {
        return User::factory()->phoneUnverified()->create();
    }

    private function requestCode(User $user): string
    {
        $this->actingAs($user)->post(route('phone.verification.send'))->assertRedirect(route('phone.verification.notice'));

        return $this->sender->last()->code;
    }

    public function test_the_notice_page_is_shown_once_email_is_verified(): void
    {
        $user = $this->emailVerifiedInvestor();

        $this->actingAs($user)->get(route('phone.verification.notice'))
            ->assertOk()
            ->assertSee('Verify your phone number')
            ->assertSee($user->phone);
    }

    public function test_phone_verification_requires_email_verification_first(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)->get(route('phone.verification.notice'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->post(route('phone.verification.send'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => '123456'])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('phone_verification_codes', 0);
        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_phone_verification_requires_authentication(): void
    {
        $this->get(route('phone.verification.notice'))->assertRedirect(route('login'));
        $this->post(route('phone.verification.send'))->assertRedirect(route('login'));
        $this->post(route('phone.verification.confirm'), ['code' => '123456'])->assertRedirect(route('login'));
    }

    public function test_staff_accounts_are_refused(): void
    {
        $staff = User::factory()->staff()->phoneUnverified()->create();

        $this->actingAs($staff)->get(route('phone.verification.notice'))->assertForbidden();
        $this->actingAs($staff)->post(route('phone.verification.send'))->assertForbidden();
        $this->actingAs($staff)->post(route('phone.verification.confirm'), ['code' => '123456'])->assertForbidden();
    }

    public function test_requesting_a_code_stores_only_a_hash_and_delivers_through_the_sender(): void
    {
        $user = $this->emailVerifiedInvestor();

        $code = $this->requestCode($user);

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertSame($user->phone, $this->sender->last()->phone);

        $challenge = PhoneVerificationCode::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotSame($code, $challenge->code_hash);
        $this->assertTrue(Hash::check($code, $challenge->code_hash));
        $this->assertTrue($challenge->expires_at->isFuture());
        $this->assertNull($challenge->consumed_at);

        $record = $this->assertAuditRecorded(InvestorAuditActions::PHONE_VERIFICATION_REQUESTED, $user->id);
        $this->assertStringNotContainsString($code, json_encode($record->properties));
    }

    public function test_the_code_is_never_written_to_the_application_log(): void
    {
        $handler = new TestHandler;
        Log::getLogger()->pushHandler($handler);

        $user = $this->emailVerifiedInvestor();
        $code = $this->requestCode($user);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code]);

        foreach ($handler->getRecords() as $logRecord) {
            $this->assertStringNotContainsString($code, $logRecord->message);
            $this->assertStringNotContainsString($code, json_encode($logRecord->context));
        }
    }

    public function test_requesting_a_new_code_invalidates_the_previous_one(): void
    {
        $user = $this->emailVerifiedInvestor();

        $first = $this->requestCode($user);
        $second = $this->requestCode($user);

        $this->assertDatabaseCount('phone_verification_codes', 1);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $first])
            ->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->phone_verified_at);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $second])
            ->assertRedirect(route('investor.profile'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_a_correct_code_verifies_the_phone_only(): void
    {
        Event::fake([InvestorPhoneVerified::class]);
        $user = $this->emailVerifiedInvestor();
        $emailVerifiedAt = $user->email_verified_at;
        $code = $this->requestCode($user);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code])
            ->assertRedirect(route('investor.profile'))
            ->assertSessionHas('status', 'phone-verified');

        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);
        $this->assertTrue($emailVerifiedAt->equalTo($user->email_verified_at));

        $challenge = PhoneVerificationCode::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($challenge->consumed_at);

        $record = $this->assertAuditRecorded(InvestorAuditActions::PHONE_VERIFIED, $user->id);
        $this->assertNull($record->properties['before']['phone_verified_at']);
        $this->assertNotNull($record->properties['after']['phone_verified_at']);

        Event::assertDispatched(InvestorPhoneVerified::class, fn ($event) => $event->user->is($user));
    }

    public function test_a_wrong_code_is_rejected_and_audited(): void
    {
        $user = $this->emailVerifiedInvestor();
        $code = $this->requestCode($user);
        $wrong = $code === '000000' ? '111111' : '000000';

        $this->actingAs($user)->from(route('phone.verification.notice'))
            ->post(route('phone.verification.confirm'), ['code' => $wrong])
            ->assertRedirect(route('phone.verification.notice'))
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
        $this->assertSame(1, PhoneVerificationCode::query()->where('user_id', $user->id)->value('failed_attempts'));
        $this->assertAuditRecorded(InvestorAuditActions::PHONE_VERIFICATION_FAILED, $user->id);
        $this->assertAuditNotRecorded(InvestorAuditActions::PHONE_VERIFIED);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        $user = $this->emailVerifiedInvestor();
        $code = $this->requestCode($user);

        $this->travel((int) config('onboarding.phone_verification.ttl_minutes') + 1)->minutes();

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_a_consumed_code_cannot_be_replayed(): void
    {
        $user = $this->emailVerifiedInvestor();
        $code = $this->requestCode($user);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code])->assertRedirect(route('investor.profile'));

        // Simulate the phone fact being reset while the consumed row remains.
        $user->forceFill(['phone_verified_at' => null])->save();

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_another_users_code_cannot_verify_this_account(): void
    {
        $victim = $this->emailVerifiedInvestor();
        $attacker = $this->emailVerifiedInvestor();

        $victimCode = $this->requestCode($victim);

        $this->actingAs($attacker)->post(route('phone.verification.confirm'), ['code' => $victimCode])
            ->assertSessionHasErrors('code');

        $this->assertNull($attacker->fresh()->phone_verified_at);
        $this->assertNull($victim->fresh()->phone_verified_at);
    }

    public function test_the_code_input_is_validated(): void
    {
        $user = $this->emailVerifiedInvestor();
        $this->requestCode($user);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => '12'])->assertSessionHasErrors('code');
        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => 'abcdef'])->assertSessionHasErrors('code');
        $this->actingAs($user)->post(route('phone.verification.confirm'), [])->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_confirmation_attempts_are_throttled(): void
    {
        $user = $this->emailVerifiedInvestor();
        $this->requestCode($user);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => '000000'])->assertRedirect();
        }

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => '000000'])->assertStatus(429);
    }

    public function test_code_requests_are_throttled(): void
    {
        $user = $this->emailVerifiedInvestor();

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->post(route('phone.verification.send'))->assertRedirect();
        }

        $this->actingAs($user)->post(route('phone.verification.send'))->assertStatus(429);
    }

    public function test_an_already_verified_investor_is_sent_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('phone.verification.notice'))->assertRedirect(route('investor.profile'));
        $this->actingAs($user)->post(route('phone.verification.send'))->assertRedirect(route('investor.profile'));
        $this->assertDatabaseCount('phone_verification_codes', 0);
    }

    public function test_the_application_service_refuses_non_investors(): void
    {
        $staff = User::factory()->staff()->phoneUnverified()->create();

        $this->expectException(NotAnInvestor::class);

        $this->app->make(RequestPhoneVerification::class)->handle($staff);
    }

    public function test_a_welcome_is_sent_when_email_and_phone_are_both_verified(): void
    {
        Notification::fake();
        $user = $this->emailVerifiedInvestor();
        $code = $this->requestCode($user);

        $this->actingAs($user)->post(route('phone.verification.confirm'), ['code' => $code])
            ->assertRedirect(route('investor.profile'));

        Notification::assertSentTo($user, InvestorAccountVerified::class, function (InvestorAccountVerified $notification) use ($user): bool {
            $mail = $notification->toMail($user);
            $html = $mail->render();

            $this->assertSame('Welcome to IOMP — your account is verified', $mail->subject);
            $this->assertStringContainsString('images/gipa-logo.png', $html);
            $this->assertStringContainsString(route('home'), $html);
            $this->assertStringContainsString(route('investor.profile'), $html);
            $this->assertStringContainsString(route('contact.create'), $html);
            $this->assertStringContainsString('successfully verified', $html);
            $this->assertStringContainsString('business profile', $html);

            return true;
        });
    }
}
