<?php

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\InvestorEngagement\Presentation\Http\Controllers\InvestorEngagementController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\InvestorAccountController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\InvestorDashboardController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\InvestorProfileController;
use App\Modules\Shared\Presentation\Http\Controllers\NotificationController;
use App\Modules\Opportunity\Presentation\Http\Controllers\InvestorOpportunityController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\PhoneVerificationController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

// Self-registration: investors only (ADR-0005). There is no staff variant.
Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:registration')
        ->name('register.store');
});

Route::middleware(['auth', 'investor', 'verified'])->prefix('phone')->group(function (): void {
    Route::get('/verify', [PhoneVerificationController::class, 'notice'])
        ->name('phone.verification.notice');
    Route::post('/verify/send', [PhoneVerificationController::class, 'send'])
        ->middleware('throttle:phone-verification-send')
        ->name('phone.verification.send');
    Route::post('/verify', [PhoneVerificationController::class, 'confirm'])
        ->middleware('throttle:phone-verification-confirm')
        ->name('phone.verification.confirm');
});

Route::middleware(['auth', 'investor.verified'])->prefix('investor')->name('investor.')->group(function (): void {
    Route::get('/profile', [InvestorProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [InvestorProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [InvestorProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('investor.profile')->group(function (): void {
    Route::get('/dashboard', [InvestorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/opportunities', [InvestorOpportunityController::class, 'index'])->name('opportunities');
    Route::get('/map', [InvestorOpportunityController::class, 'map'])->name('map');
    Route::get('/map/opportunities', [InvestorOpportunityController::class, 'geojson'])->name('map.opportunities');
    Route::get('/map/nearby', [InvestorOpportunityController::class, 'nearby'])->name('map.nearby');
    Route::get('/opportunities/{opportunity}', [InvestorOpportunityController::class, 'show'])->name('opportunities.show');
    Route::post('/opportunities/{opportunity}/inquiries', [InvestorOpportunityController::class, 'inquire'])
        ->middleware('can:'.AccessPermissions::INVESTOR_ENGAGEMENT)
        ->name('opportunities.inquire');
    Route::post('/opportunities/{opportunity}/interest', [InvestorOpportunityController::class, 'interest'])
        ->middleware('can:'.AccessPermissions::INVESTOR_ENGAGEMENT)
        ->name('opportunities.interest');
    Route::get('/engagement', [InvestorEngagementController::class, 'index'])->name('engagement');
    Route::get('/engagement/{engagement}', [InvestorEngagementController::class, 'show'])->name('engagement.show');
    Route::post('/engagement/{engagement}/messages', [InvestorEngagementController::class, 'message'])->name('engagement.messages.store');
    Route::get('/account', [InvestorAccountController::class, 'show'])->name('account');
    Route::put('/account/personal', [InvestorAccountController::class, 'updatePersonal'])->name('account.personal');
    Route::put('/account/email', [InvestorAccountController::class, 'updateEmail'])->name('account.email');
    Route::put('/account/phone', [InvestorAccountController::class, 'updatePhone'])->name('account.phone');
    Route::put('/account/password', [InvestorAccountController::class, 'updatePassword'])->name('account.password');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });
});
