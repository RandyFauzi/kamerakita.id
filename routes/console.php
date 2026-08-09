<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('partners:sync-activity-status')->dailyAt('00:10');
Schedule::command('partners:notify-inactive')->dailyAt('09:00');

// Mailbox Cleanup (Karena kita akan mencoba lagi Webhook Catch-all, ini hanya jalan 1x sehari)
Schedule::command('app:pull-mailbox-emails')->daily();
Schedule::command('app:clean-expired-emails')->daily();
