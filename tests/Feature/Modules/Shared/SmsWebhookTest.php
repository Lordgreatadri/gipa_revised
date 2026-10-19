<?php

namespace Tests\Feature\Modules\Shared;

use App\Modules\Shared\Infrastructure\Sms\SmsDeliveryEvent;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;
use App\Modules\Shared\Infrastructure\Sms\Webhooks\Jobs\ProcessSmsStatusWebhookJob;
use App\Modules\Shared\Infrastructure\Sms\Webhooks\SmsWebhookCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\WebhookClient\Models\WebhookCall;
use Tests\TestCase;

class SmsWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_frog_callback_without_a_valid_token_is_rejected(): void
    {
        $this->frogConfig();

        $this->postJson(route('webhook-client-frog-sms'), [
            'msgid' => 'SMS-1',
            'status' => 'DELIVRD',
        ])->assertForbidden();

        $this->assertDatabaseCount('webhook_calls', 0);
    }

    public function test_a_frog_callback_with_the_shared_token_marks_the_message_delivered(): void
    {
        $this->frogConfig();
        $message = $this->message();

        $this->postJson(route('webhook-client-frog-sms').'?token=frog-webhook-secret', [
            'msgid' => $message->reference,
            'status' => 'DELIVRD',
            'reason' => 'DELIVRD',
            'destination' => '233241234567',
            'statusdate' => '2026-09-10T14:59:14.143+00:00',
        ])->assertOk();

        $message->refresh();
        $this->assertSame(SmsMessage::DELIVERED, $message->status);
        $this->assertNotNull($message->delivered_at);

        $call = SmsWebhookCall::query()->firstOrFail();
        $this->assertStringNotContainsString('frog-webhook-secret', $call->url);
        $this->assertArrayNotHasKey('token', (array) $call->payload);

        $event = SmsDeliveryEvent::query()->firstOrFail();
        $this->assertSame('DELIVRD', $event->raw_status);
        $this->assertSame($message->id, $event->sms_message_id);
    }

    public function test_a_frog_callback_accepts_an_hmac_signature_header(): void
    {
        $this->frogConfig();
        $message = $this->message();
        $payload = ['msgid' => $message->reference, 'status' => 'UNDELIV', 'reason' => 'Absent subscriber'];
        $body = json_encode($payload);

        $this->call(
            'POST',
            route('webhook-client-frog-sms'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_FROG_SIGNATURE' => hash_hmac('sha256', $body, 'frog-webhook-secret'),
            ],
            $body,
        )->assertOk();

        $message->refresh();
        $this->assertSame(SmsMessage::UNDELIVERED, $message->status);
        $this->assertSame('Absent subscriber', $message->error);
    }

    public function test_a_twilio_callback_requires_a_valid_signature(): void
    {
        $this->twilioConfig();

        $this->post(route('webhook-client-twilio-sms'), [
            'MessageSid' => 'SM1',
            'MessageStatus' => 'delivered',
        ], ['X-Twilio-Signature' => 'not-the-right-signature'])->assertForbidden();

        $this->assertDatabaseCount('webhook_calls', 0);
    }

    public function test_a_twilio_callback_with_a_valid_signature_updates_the_message(): void
    {
        $this->twilioConfig();
        $message = $this->message(['provider' => 'twilio', 'provider_message_id' => 'SM12345']);
        $url = route('webhook-client-twilio-sms');
        $payload = ['MessageSid' => 'SM12345', 'MessageStatus' => 'delivered'];

        $this->post($url, $payload, [
            'X-Twilio-Signature' => $this->twilioSignature($url, $payload, 'twilio-auth-token'),
        ])->assertOk();

        $message->refresh();
        $this->assertSame(SmsMessage::DELIVERED, $message->status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_a_late_report_cannot_downgrade_a_delivered_message(): void
    {
        $message = $this->message(['status' => SmsMessage::DELIVERED, 'delivered_at' => now()]);

        (new ProcessSmsStatusWebhookJob(WebhookCall::query()->create([
            'name' => 'frog-sms',
            'url' => 'https://gipa_prod.test/webhooks/sms/frog',
            'payload' => ['msgid' => $message->reference, 'status' => 'WAITING'],
        ])))->handle();

        $this->assertSame(SmsMessage::DELIVERED, $message->fresh()->status);
    }

    public function test_a_callback_for_an_unknown_message_is_stored_without_error(): void
    {
        (new ProcessSmsStatusWebhookJob(WebhookCall::query()->create([
            'name' => 'frog-sms',
            'url' => 'https://gipa_prod.test/webhooks/sms/frog',
            'payload' => ['msgid' => 'unknown-reference', 'status' => 'DELIVRD'],
        ])))->handle();

        $event = SmsDeliveryEvent::query()->firstOrFail();
        $this->assertNull($event->sms_message_id);
        $this->assertSame('unknown-reference', $event->provider_message_id);
    }

    public function test_a_callback_without_a_message_id_is_not_stored(): void
    {
        $this->frogConfig();

        $this->postJson(route('webhook-client-frog-sms').'?token=frog-webhook-secret', [
            'ping' => 'health-check',
        ])->assertOk();

        $this->assertDatabaseCount('webhook_calls', 0);
        $this->assertDatabaseCount('sms_delivery_events', 0);
    }

    private function frogConfig(): SmsProviderConfig
    {
        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => 'frog',
            'is_active' => true,
            'credentials' => ['username' => 'gipa-ops', 'api_key' => 'frog-key'],
            'webhook_secret' => 'frog-webhook-secret',
        ])->save();

        return $config;
    }

    private function twilioConfig(): SmsProviderConfig
    {
        $config = new SmsProviderConfig;
        $config->forceFill([
            'provider' => 'twilio',
            'is_active' => true,
            'credentials' => ['account_sid' => 'AC123', 'auth_token' => 'twilio-auth-token'],
            'webhook_secret' => 'unused-twilio-secret',
        ])->save();

        return $config;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function message(array $overrides = []): SmsMessage
    {
        $message = new SmsMessage;
        $message->forceFill(array_merge([
            'reference' => 'SMS-20260910-ABCDEFGH',
            'provider' => 'frog',
            'context' => 'sms.test',
            'recipient' => '233241234567',
            'body' => 'Delivery report test.',
            'status' => SmsMessage::SENT,
            'provider_message_id' => 'SMS-20260910-ABCDEFGH',
            'sent_at' => now(),
        ], $overrides));
        $message->save();

        return $message;
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function twilioSignature(string $url, array $payload, string $token): string
    {
        ksort($payload);
        $data = $url;

        foreach ($payload as $key => $value) {
            $data .= $key.$value;
        }

        return base64_encode(hash_hmac('sha1', $data, $token, true));
    }
}
