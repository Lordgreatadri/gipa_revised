<?php

namespace App\Modules\Shared\Infrastructure\Sms\Contracts;

use App\Modules\Shared\Infrastructure\Sms\Data\OutgoingSms;
use App\Modules\Shared\Infrastructure\Sms\Data\ProviderBalance;
use App\Modules\Shared\Infrastructure\Sms\Data\SmsResult;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderConfig;

interface SmsProvider
{
    public function name(): string;

    public function send(OutgoingSms $message): SmsResult;

    public function balance(): ProviderBalance;

    public function config(): SmsProviderConfig;
}
