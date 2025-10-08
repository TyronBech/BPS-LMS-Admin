<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Example default command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Your scheduled tasks

// Auto time-out: daily at 6:29 PM
Schedule::command('app:auto-time-out')->dailyAt('18:29');

// Database backup: daily at 10:00 AM, 2:00 PM, and 6:00 PM
Schedule::command('app:db-backup')->dailyAt('10:00');
Schedule::command('app:db-backup')->dailyAt('14:00');
Schedule::command('app:db-backup')->dailyAt('18:00');

// Clean old backups: daily at 2:00 AM
Schedule::command('backup:clean')->dailyAt('02:00');
