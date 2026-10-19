<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification;

use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\PhoneVerificationMessage;
use App\Modules\InvestorOnboarding\Domain\Exceptions\PhoneVerificationDeliveryFailed;
use App\Modules\Shared\Infrastructure\Sms\SmsGateway;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;

/**
 * Sends the one-time code through whatever SMS gateway an administrator
 * has switched on. Frog, Twilio, the log provider, and any later catalogue
 * entry are chosen by priority. Each gateway receives the phone number in
 * its own format. The code is not written to the application log.
 */
final class SmsPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function send(PhoneVerificationMessage $message): void
    {
        $minutes = (int) config('onboarding.phone_verification.ttl_minutes', 10);
        $body = 'GIPA verification code: '.$message->code
            .'. It expires in '.$minutes.' minutes. Do not share this code.';

        $sent = $this->gateway->sendImmediate($message->phone, $body, 'investor.phone_verification');

        if ($sent === null || $sent->status !== SmsMessage::SENT) {
            throw PhoneVerificationDeliveryFailed::make();
        }
    }
}
