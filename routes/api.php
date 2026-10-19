<?php

use App\Modules\Shared\Presentation\Http\Controllers\Api\V1\AccountController;
use App\Modules\Shared\Presentation\Http\Controllers\Api\V1\AuthTokenController;
use App\Modules\Shared\Presentation\Http\Controllers\Api\V1\OpenApiController;
use App\Modules\Shared\Presentation\Http\Controllers\Api\V1\OpportunityApiController;
use App\Modules\Shared\Presentation\Http\Controllers\Api\V1\ReferenceController;
use App\Modules\Shared\Presentation\Http\Controllers\ApiStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| /api/v1 (ADR-0004, ADR-0005)
|--------------------------------------------------------------------------
|
| Public reference data, JWT access for the authenticated account, and the
| verified-investor view of active opportunities. Staff workflow, contact
| details, SMS credentials, and unpublished records stay off this surface.
|
*/

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::get('/status', ApiStatusController::class)->name('status');
    Route::get('/openapi.json', OpenApiController::class)->name('openapi');

    Route::get('/regions', [ReferenceController::class, 'regions'])->name('regions');
    Route::get('/districts', [ReferenceController::class, 'districts'])->name('districts');
    Route::get('/sectors', [ReferenceController::class, 'sectors'])->name('sectors');
    Route::get('/sub-sectors', [ReferenceController::class, 'subSectors'])->name('sub-sectors');
    Route::get('/enterprise-types', [ReferenceController::class, 'enterpriseTypes'])->name('enterprise-types');

    Route::post('/auth/token', [AuthTokenController::class, 'store'])->middleware('throttle:api-auth')->name('auth.token');
    Route::post('/auth/refresh', [AuthTokenController::class, 'refresh'])->middleware('throttle:api-auth')->name('auth.refresh');
    Route::post('/auth/revoke', [AuthTokenController::class, 'revoke'])->middleware('throttle:api-auth')->name('auth.revoke');

    Route::middleware('auth.api')->group(function (): void {
        Route::get('/me', [AccountController::class, 'show'])->name('me');

        Route::middleware(['investor', 'investor.verified', 'investor.profile'])->group(function (): void {
            Route::get('/opportunities', [OpportunityApiController::class, 'index'])->name('opportunities.index');
            Route::get('/engagements', [OpportunityApiController::class, 'engagements'])->name('engagements.index');
            Route::get('/map/opportunities', [OpportunityApiController::class, 'viewport'])->name('map.opportunities');
            Route::get('/map/nearby', [OpportunityApiController::class, 'nearby'])->name('map.nearby');
            Route::get('/opportunities/{opportunity}', [OpportunityApiController::class, 'show'])->name('opportunities.show');
            Route::post('/opportunities/{opportunity}/inquiries', [OpportunityApiController::class, 'inquire'])->name('opportunities.inquiries.store');
            Route::post('/opportunities/{opportunity}/interest', [OpportunityApiController::class, 'interest'])->name('opportunities.interest.store');
        });
    });
});
