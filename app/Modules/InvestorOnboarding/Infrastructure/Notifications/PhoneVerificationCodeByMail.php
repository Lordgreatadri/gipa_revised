<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Used only by MailPhoneVerificationSender (non-production).
 */
class PhoneVerificationCodeByMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly Carbon $expiresAt,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        logger()->info('NullPhoneVerificationSender: Message not sent.', ['code' => $this->code, 'expiresAt' => $this->expiresAt]); 
        return (new MailMessage)
            ->subject(__('Your IOMP phone verification code'))
            ->line(__('Development environment: this code would normally be sent by SMS.'))
            ->line(__('Your verification code is: :code', ['code' => $this->code]))
            ->line(__('It expires at :time.', ['time' => $this->expiresAt->toDayDateTimeString()]));
    }
}
