<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('partners:sync-activity-status')->dailyAt('00:10');
Schedule::command('partners:notify-inactive')->dailyAt('09:00');

// Mailbox Catch-all Polling
Schedule::command('app:pull-mailbox-emails')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('app:clean-expired-emails')->daily();
