<?php

namespace App\Modules\PublicPortal\Infrastructure\Notifications;

use App\Modules\PublicPortal\Domain\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactEnquiryReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ContactUs $enquiry, public readonly string $reply) {}

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
            ->subject('Reply to your enquiry ('.$this->enquiry->reference.')')
            ->greeting('Hello '.$this->enquiry->name.',')
            ->line('GIPA has replied to your enquiry '.$this->enquiry->reference.'.')
            ->line($this->reply)
            ->line('Please quote the reference in any follow-up.')
            ->salutation('Warm regards, GIPA Investment Services');
    }
}
