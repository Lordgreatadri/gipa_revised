<?php

namespace Tests\Feature\Modules\InvestorOnboarding;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Models\PhoneVerificationCode;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhoneVerificationSmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['onboarding.phone_verification.driver' => 'sms']);
    }

    public function test_a_code_is_texted_through_the_active_log_provider_without_a_network_call(): void
    {
        Http::fake();
        $this->provider('log', 10);

        $user = User::factory()->phoneUnverified()->create();

        $this->actingAs($user)
            ->post(route('phone.verification.send'))
            ->assertRedirect(route('phone.verification.notice'));

        $message = SmsMessage::query()->firstOrFail();
        $this->assertSame(SmsMessage::SENT, $message->status);
        $this->assertSame('log', $message->provider);
        $this->assertSame('investor.phone_verification', $message->context);
        $this->assertSame($this->digits($user->phone), $message->recipient);
        $this->assertMatchesRegularExpression('/^\d+$/', $message->recipient);

        $code = $this->codeFrom($message->body);
        $this->assertTrue(Hash::check($code, PhoneVerificationCode::query()->firstOrFail()->code_hash));
        Http::assertNothingSent();
    }

    public function test_a_frog_transport_failure_falls_through_to_the_log_provider(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'frogapi.wigal.com.gh')) {
                return Http::response('Method Not Allowed', 405);
            }

            return Http::response('unexpected', 500);
        });

        $this->provider('frog', 1, [
            'username' => 'gateway-user',
            'api_key' => 'secret-key-value',
        ]);
        $this->provider('log', 2);

        $user = User::factory()->phoneUnverified()->create();

        $this->actingAs($user)->post(route('phone.verification.send'))->assertRedirect();

        $message = SmsMessage::query()->firstOrFail();
        $this->assertSame(SmsMessage::SENT, $message->status);
        $this->assertSame('log', $message->provider);

        Http::assertSent(function ($request): bool {
            $destination = $request->data()['destinations'][0]['destination'] ?? '';

            return is_string($destination)
                && $destination !== ''
                && ! str_starts_with($destination, '+')
                && ctype_digit($destination);
        });
    }

    public function test_a_missing_gateway_does_not_leave_a_live_code(): void
    {
        $user = User::factory()->phoneUnverified()->create();

        $this->actingAs($user)
            ->from(route('phone.verification.notice'))
            ->post(route('phone.verification.send'))
            ->assertRedirect(route('phone.verification.notice'))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('phone_verification_codes', 0);
        $this->assertDatabaseCount('sms_messages', 0);
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function provider(string $name, int $priority, array $credentials = []): void
    {
        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => $name,
            'is_active' => true,
            'priority' => $priority,
            'credentials' => $credentials,
            'sender_id' => 'GIPA',
        ])->save();
    }

    private function digits(string $phone): string
    {
        return ltrim($phone, '+');
    }

    private function codeFrom(string $body): string
    {
        preg_match('/\d{6}/', $body, $matches);

        return $matches[0];
    }
}
