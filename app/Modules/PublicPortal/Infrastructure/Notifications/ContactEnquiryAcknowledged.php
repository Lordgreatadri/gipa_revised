<?php

namespace App\Modules\PublicPortal\Infrastructure\Notifications;

use App\Modules\PublicPortal\Domain\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms receipt to the person who submitted the public enquiry.
 * Mail only: SMS is deferred with the rest of the messaging stack.
 */
class ContactEnquiryAcknowledged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ContactUs $enquiry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We have received your enquiry ('.$this->enquiry->reference.')')
            ->greeting('Hello '.$this->enquiry->name.',')
            ->line('Thank you for contacting the Ghana Investment Promotion Authority.')
            ->line('We have logged your enquiry under reference **'.$this->enquiry->reference.'** and a member of our investment services team will get in touch with you shortly.')
            ->line('Subject: '.$this->enquiry->subject)
            ->line('Please quote the reference above in any follow-up correspondence.')
            ->action('Return to IOMP', route('home'))
            ->salutation('Warm regards, GIPA Investment Services');
    }
}
