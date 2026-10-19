<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\Providers;

use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Application\Listeners\PublishInvestorEmailVerified;
use App\Modules\InvestorOnboarding\Application\Listeners\SendEmailVerificationOnRegistration;
use App\Modules\InvestorOnboarding\Application\Listeners\SendInvestorWelcome;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorPhoneVerified;
use App\Modules\InvestorOnboarding\Domain\Events\InvestorRegistered;
use App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification\ArrayPhoneVerificationSender;
use App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification\MailPhoneVerificationSender;
use App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification\NullPhoneVerificationSender;
use App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification\SmsPhoneVerificationSender;
use App\Modules\Shared\Infrastructure\RateLimits\ThrottleSpec;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class InvestorOnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArrayPhoneVerificationSender::class);

        $this->app->bind(PhoneVerificationSender::class, function ($app) {
            $driver = (string) config('onboarding.phone_verification.driver', 'null');

            return match ($driver) {
                'null' => $app->make(NullPhoneVerificationSender::class),
                'array' => $app->make(ArrayPhoneVerificationSender::class),
                'mail' => $app->make(MailPhoneVerificationSender::class),
                'sms' => $app->make(SmsPhoneVerificationSender::class),
                default => throw new InvalidArgumentException("Unknown phone verification driver [{$driver}]."),
            };
        });
    }

    public function boot(): void
    {
        $this->registerRateLimiters();

        $this->loadViewsFrom(dirname(__DIR__, 2).'/Presentation/Views', 'investor-onboarding');

        Event::listen(InvestorRegistered::class, SendEmailVerificationOnRegistration::class);
        Event::listen(Verified::class, PublishInvestorEmailVerified::class);
        Event::listen(InvestorPhoneVerified::class, SendInvestorWelcome::class);
    }

    /**
     * Distinct buckets per flow (technical brute-force defaults; AMB-02 owns
     * the business policy).
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('registration', fn (Request $request) => ThrottleSpec::limit(
            (string) config('onboarding.registration.throttle', '10,1'),
            ThrottleSpec::bucketFor($request),
        ));

        RateLimiter::for('phone-verification-send', fn (Request $request) => ThrottleSpec::limit(
            (string) config('onboarding.phone_verification.send_throttle', '3,10'),
            ThrottleSpec::bucketFor($request),
        ));

        RateLimiter::for('phone-verification-confirm', fn (Request $request) => ThrottleSpec::limit(
            (string) config('onboarding.phone_verification.confirm_throttle', '5,1'),
            ThrottleSpec::bucketFor($request),
        ));
    }
}
