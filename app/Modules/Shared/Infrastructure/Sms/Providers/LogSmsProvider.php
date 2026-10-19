<?php

namespace App\Modules\Shared\Infrastructure\Sms\Providers;

use App\Modules\Shared\Infrastructure\Sms\Data\OutgoingSms;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\Data\SmsResult;

class LogSmsProvider extends AbstractSmsProvider
{
    public function name(): string
    {
        return 'log';
    }

    public function send(OutgoingSms $message): SmsResult
    {
        $this->log('info', 'SMS captured by the log provider and was not transmitted', [
            'reference' => $message->reference,
        ]);

        return SmsResult::success('logged', 'Recorded locally and not transmitted.', $message->reference);
    }

    public function balance(): ProviderBalance
    {
        return ProviderBalance::unavailable($this->name(), 'The log provider does not consume credit.');
    }
}
