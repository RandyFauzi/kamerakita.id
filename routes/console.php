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
Schedule::command('mailbox:prune-quarantine')->daily();

// Existing Hostinger schedule:run cron is sufficient. The HTTP button only enqueues.
Schedule::command('robotics1618:sync --request')->everyTwoHours()->timezone('Asia/Makassar')
    ->when(fn () => config('services.robotics1618.enabled'));
Schedule::command('robotics1618:sync --work')->everyMinute()->withoutOverlapping(15)->runInBackground()
    ->when(fn () => config('services.robotics1618.enabled'));

// Atlas is synchronized by the versioned Playwright bot on its Windows host.
// Do not schedule the legacy HTTP scraper, which targets the obsolete schema.
