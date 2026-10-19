<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\WebhookClient\Models\WebhookCall;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('model:prune', [
    '--model' => [WebhookCall::class],
])->daily();

Schedule::command('sms:watch-balances')->daily();

Schedule::command('backup:database')
    ->dailyAt('02:15')
    ->timezone('Africa/Accra')
    ->withoutOverlapping();

Schedule::command('backup:clean', ['--disable-notifications' => true])
    ->dailyAt('03:00')
    ->timezone('Africa/Accra')
    ->withoutOverlapping();
