<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Example default command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Your scheduled tasks

// Auto time-out: daily at 4:00 PM
Schedule::command('app:auto-time-out')->dailyAt('16:00');

// Database backup: daily at 1:00 AM
Schedule::command('app:db-backup')->dailyAt('01:00');

// Cleanup old backups: once a week, Sunday at 2:00 AM
Schedule::command('backup:clean')->weeklyOn(0, '02:00');
