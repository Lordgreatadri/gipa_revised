<?php

use App\Modules\Assistant\Infrastructure\Providers\AssistantServiceProvider;
use App\Modules\Backup\Infrastructure\Providers\BackupServiceProvider;
use App\Modules\Audit\Infrastructure\Providers\AuditServiceProvider;
use App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider;
use App\Modules\InvestorOnboarding\Infrastructure\Providers\InvestorOnboardingServiceProvider;
use App\Modules\PublicPortal\Infrastructure\Providers\PublicPortalServiceProvider;
use App\Modules\Shared\Infrastructure\Providers\SharedServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    // Bounded-context modules (ADR-0001, ADR-0002, decision D-1).
    SharedServiceProvider::class,
    AuditServiceProvider::class,
    IdentityServiceProvider::class,
    InvestorOnboardingServiceProvider::class,
    PublicPortalServiceProvider::class,
    AssistantServiceProvider::class,
    BackupServiceProvider::class,
];
