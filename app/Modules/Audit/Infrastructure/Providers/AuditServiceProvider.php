<?php

namespace App\Modules\Audit\Infrastructure\Providers;

use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Audit\Infrastructure\ActivitylogAuditRecorder;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditRecorder::class, ActivitylogAuditRecorder::class);
    }
}
