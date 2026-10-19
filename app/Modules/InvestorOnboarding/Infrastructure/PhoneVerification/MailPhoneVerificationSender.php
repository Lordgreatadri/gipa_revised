<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification;

use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\PhoneVerificationMessage;
use App\Modules\InvestorOnboarding\Infrastructure\Notifications\PhoneVerificationCodeByMail;
use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

/**
 * Non-production convenience: delivers the phone code to the investor's
 * e-mail address through the mail system (locally MAIL_MAILER=log) so the
 * end-to-end flow can be exercised before an SMS provider exists.
 *
 * It refuses to operate in production because it would collapse the phone
 * trust signal into the e-mail trust signal.
 */
final class MailPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(private readonly Application $app) {}

    public function send(PhoneVerificationMessage $message): void
    {
        if ($this->app->isProduction()) {
            throw new RuntimeException('The mail phone-verification driver is not permitted in production.');
        }

        $message->user->notify(new PhoneVerificationCodeByMail($message->code, $message->expiresAt));
    }
}
