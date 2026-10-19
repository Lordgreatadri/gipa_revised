<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\Notifications;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent once e-mail and phone are both verified. The phone step is last,
 * so this is the welcome that opens the business-profile task.
 */
class InvestorAccountVerified extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        return (new MailMessage)
            ->subject('Welcome to IOMP — your account is verified')
            ->markdown('investor-onboarding::mail.account-verified', [
                'user' => $notifiable,
                'profileUrl' => route('investor.profile'),
                'supportUrl' => route('contact.create'),
            ]);
    }
}
