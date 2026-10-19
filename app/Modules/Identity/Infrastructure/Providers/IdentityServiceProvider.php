<?php

namespace App\Modules\Identity\Infrastructure\Providers;

use App\Modules\Identity\Application\Listeners\RecordAuthenticationAudit;
use App\Modules\Shared\Infrastructure\RateLimits\ThrottleSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('email-verification-resend', fn (Request $request) => ThrottleSpec::limit(
            (string) config('onboarding.email_verification.resend_throttle', '6,1'),
            ThrottleSpec::bucketFor($request),
        ));

        RateLimiter::for('email-verification-verify', fn (Request $request) => ThrottleSpec::limit(
            '6,1',
            ThrottleSpec::bucketFor($request),
        ));

        RateLimiter::for('password-reset-request', fn (Request $request) => ThrottleSpec::limit(
            (string) config('security.password_reset.request_throttle', '5,1'),
            ThrottleSpec::bucketFor($request),
        ));

        RateLimiter::for('password-reset', fn (Request $request) => ThrottleSpec::limit(
            (string) config('security.password_reset.reset_throttle', '5,1'),
            ThrottleSpec::bucketFor($request),
        ));

        $this->loadViewsFrom(dirname(__DIR__, 2).'/Presentation/Views', 'identity');

        Event::subscribe(RecordAuthenticationAudit::class);
    }
}
