<?php

namespace Tests\Feature\Modules\PublicPortal;

use App\Modules\PublicPortal\Domain\ContactAuditActions;
use App\Modules\PublicPortal\Domain\Models\ContactUs;
use App\Modules\PublicPortal\Infrastructure\Notifications\ContactEnquiryAcknowledged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactEnquiryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ama Mensah',
            'email' => 'ama@example.com',
            'phone_number' => '0241234567',
            'available_time_for_call' => 'Mornings 8am–12pm',
            'subject' => 'Question about investor registration',
            'message' => 'We would like more detail on how to complete e-mail and phone verification.',
            'consent' => '1',
        ], $overrides);
    }

    public function test_the_public_contact_page_is_reachable_without_authentication(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('Contact us')
            ->assertSee('How can we help?')
            ->assertDontSee('Browse investment opportunities');
    }

    public function test_an_enquiry_is_stored_and_the_sender_is_acknowledged_by_email_only(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('contact_reference');

        $enquiry = ContactUs::query()->firstOrFail();
        $this->assertSame('Ama Mensah', $enquiry->name);
        $this->assertSame('ama@example.com', $enquiry->email);
        $this->assertSame(ContactUs::STATUS_NEW, $enquiry->status);
        $this->assertStringStartsWith('CON-', $enquiry->reference);
        $this->assertNotNull($enquiry->ip_address);
        $this->assertTrue($enquiry->email_sent);
        $this->assertFalse($enquiry->sms_sent);
        $this->assertNull($enquiry->sms_sent_at);

        Notification::assertSentTo($enquiry, ContactEnquiryAcknowledged::class);
        Notification::assertCount(1);
    }

    public function test_submission_is_audited_without_the_message_body(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload())->assertRedirect();

        $enquiry = ContactUs::query()->firstOrFail();
        $record = $this->assertAuditRecorded(ContactAuditActions::ENQUIRY_SUBMITTED, $enquiry->id);
        $metadata = $record->properties['metadata'] ?? [];

        $this->assertSame($enquiry->reference, $metadata['reference'] ?? null);
        $this->assertTrue($metadata['has_phone'] ?? false);
        $this->assertArrayNotHasKey('message', $metadata);
        $this->assertStringNotContainsString('phone verification', json_encode($record->properties) ?: '');
    }

    public function test_submission_is_validated(): void
    {
        $this->post(route('contact.store'), $this->payload([
            'email' => 'not-an-email',
            'message' => 'too short',
            'phone_number' => '123',
        ]))->assertSessionHasErrors(['email', 'message', 'phone_number']);

        $this->assertSame(0, ContactUs::query()->count());
    }

    public function test_consent_is_required(): void
    {
        $payload = $this->payload();
        unset($payload['consent']);

        $this->post(route('contact.store'), $payload)->assertSessionHasErrors('consent');
        $this->assertSame(0, ContactUs::query()->count());
    }

    public function test_the_honeypot_field_blocks_bot_submissions(): void
    {
        $this->post(route('contact.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, ContactUs::query()->count());
    }
}
