<?php

namespace App\Modules\Shared\Infrastructure\Providers;

use App\Modules\Shared\Application\RequestContext;
use App\Modules\Shared\Domain\RequestSource;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestContext::class);
    }

    public function boot(): void
    {
        // Context restored inside a queued job still carries the originating
        // request id; the source becomes JOB for anything audited from there.
        Event::listen(JobProcessing::class, function (): void {
            $this->app->make(RequestContext::class)->setSource(RequestSource::Job);
        });
    }
}
