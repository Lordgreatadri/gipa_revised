<?php

use App\Modules\Assistant\Presentation\Http\Controllers\AssistantChatController;
use App\Modules\PublicPortal\Presentation\Http\Controllers\ContactController;
use App\Modules\PublicPortal\Presentation\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

/*
| Public portal. No opportunity catalogue is served here (Client-INV-04).
*/

Route::view('/', 'public.home')->name('home');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-us')
    ->name('contact.store');

Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/data-policy', [LegalController::class, 'dataPolicy'])->name('legal.data-policy');
Route::get('/guide', [LegalController::class, 'guide'])->name('platform.guide');
Route::get('/api/documentation', [LegalController::class, 'apiDocumentation'])->name('api.documentation');

Route::post('/assistant/chat', [AssistantChatController::class, 'store'])
    ->middleware('throttle:assistant')
    ->name('assistant.chat');
