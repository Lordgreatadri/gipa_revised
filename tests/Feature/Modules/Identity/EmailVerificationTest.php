<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Infrastructure\Notifications\VerifyEmailAddress;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorEmailVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user, ?string $email = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'uuid' => $user->uuid,
            'hash' => sha1($email ?? $user->email),
        ]);
    }

    public function test_notice_is_shown_to_unverified_users(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Verify your e-mail address');
    }

    public function test_notice_requires_authentication(): void
    {
        $this->get(route('verification.notice'))->assertRedirect(route('login'));
    }

    public function test_the_verification_link_uses_the_uuid_not_the_numeric_id(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $notification = new VerifyEmailAddress;
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('/email/verify/'.$user->uuid.'/', $mail->actionUrl);
        $this->assertStringNotContainsString('/email/verify/'.$user->id.'/', $mail->actionUrl);

        $html = $mail->render();
        $this->assertStringContainsString('images/gipa-logo.png', $html);
        $this->assertStringContainsString('href="'.route('home').'"', $html);
    }

    public function test_a_valid_link_verifies_the_email_only(): void
    {
        Event::fake([InvestorEmailVerified::class]);
        $user = User::factory()->registeredOnly()->create();

        $response = $this->actingAs($user)->get($this->verificationUrl($user));

        $response->assertRedirect(route('investor.dashboard'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->phone_verified_at, 'e-mail verification must not touch the phone fact');

        $this->assertAuditRecorded(IdentityAuditActions::EMAIL_VERIFIED, $user->id);
        Event::assertDispatched(InvestorEmailVerified::class, fn ($event) => $event->user->is($user));
    }

    public function test_after_email_verification_the_investor_is_routed_to_phone_verification(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)->get($this->verificationUrl($user));

        // The dashboard gate forwards to the next step.
        $this->actingAs($user->fresh())->get(route('investor.dashboard'))
            ->assertRedirect(route('phone.verification.notice'));
    }

    public function test_an_unsigned_link_is_rejected(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)
            ->get(route('verification.verify', ['uuid' => $user->uuid, 'hash' => sha1($user->email)]))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_with_the_wrong_hash_is_rejected(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user, 'someone-else@example.com'))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_for_another_account_cannot_be_completed_by_the_logged_in_user(): void
    {
        $target = User::factory()->registeredOnly()->create();
        $attacker = User::factory()->registeredOnly()->create();

        $this->actingAs($attacker)
            ->get($this->verificationUrl($target))
            ->assertForbidden();

        $this->assertNull($target->fresh()->email_verified_at);
        $this->assertNull($attacker->fresh()->email_verified_at);
    }

    public function test_an_expired_link_is_rejected(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'uuid' => $user->uuid,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_guest_cannot_use_a_verification_link(): void
    {
        $user = User::factory()->registeredOnly()->create();

        $this->get($this->verificationUrl($user))->assertRedirect(route('login'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_queues_a_new_link_and_is_audited(): void
    {
        Notification::fake();
        $user = User::factory()->registeredOnly()->create();

        $this->actingAs($user)->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmailAddress::class);
        $this->assertAuditRecorded(IdentityAuditActions::EMAIL_VERIFICATION_RESENT, $user->id);
    }

    public function test_resend_is_throttled(): void
    {
        Notification::fake();
        $user = User::factory()->registeredOnly()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
        }

        $this->actingAs($user)->post(route('verification.send'))->assertStatus(429);
    }

    public function test_verifying_twice_is_idempotent(): void
    {
        $user = User::factory()->phoneUnverified()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)->get($this->verificationUrl($user))->assertRedirect();

        $this->assertTrue($verifiedAt->equalTo($user->fresh()->email_verified_at));
        $this->assertAuditNotRecorded(IdentityAuditActions::EMAIL_VERIFIED);
    }
}
