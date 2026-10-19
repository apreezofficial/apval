<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monnify:banks-sync')
    ->daily()
    ->withoutOverlapping(30)
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('monnify:banks-sync failed'));

Schedule::command('monnify:wallet-balance-sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(4)
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('monnify:wallet-balance-sync failed'));

Schedule::command('withdrawals:expire-pending')
    ->everyMinute()
    ->withoutOverlapping(1)
    ->runInBackground()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('withdrawals:expire-pending failed'));

Schedule::command('ads:activate-scheduled-campaigns')
    ->everyMinute()
    ->withoutOverlapping(1)
    ->runInBackground()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('ads:activate-scheduled-campaigns failed'));

Schedule::command('ads:complete-expired-campaigns')
    ->everyMinute()
    ->withoutOverlapping(1)
    ->runInBackground()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('ads:complete-expired-campaigns failed'));

Schedule::command('ads:flag-budget-exhausted-campaigns')
    ->everyFiveMinutes()
    ->withoutOverlapping(4)
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('ads:flag-budget-exhausted-campaigns failed'));