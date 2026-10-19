<?php

namespace App\Modules\PublicPortal\Infrastructure\Providers;

use App\Modules\Shared\Infrastructure\RateLimits\ThrottleSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class PublicPortalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('contact-us', fn (Request $request) => ThrottleSpec::limit(
            (string) config('security.contact.throttle', '3,1'),
            'ip:'.$request->ip(),
        ));

        $this->loadViewsFrom(dirname(__DIR__, 2).'/Presentation/Views', 'public-portal');
    }
}
