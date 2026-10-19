<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $smsMessageId) {}

    public function handle(SmsGateway $gateway): void
    {
        $message = SmsMessage::query()->find($this->smsMessageId);

        if ($message === null || $message->status === SmsMessage::SENT) {
            return;
        }

        $gateway->deliver($message);
    }
}
