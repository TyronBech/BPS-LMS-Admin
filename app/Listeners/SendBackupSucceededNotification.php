<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;
use App\Notifications\BackupSucceeded;
use Illuminate\Support\Facades\Auth;

class SendBackupSucceededNotification
{
    public function handle(BackupWasSuccessful $event): void
    {
        $userName = Auth::guard('admin')->user()->first_name . ' ' . Auth::guard('admin')->user()->last_name ?? 'Admin';

        $to = config('backup.notifications.mail.to', config('mail.from.address'));
        Notification::route('mail', $to)->notify(new BackupSucceeded($userName, $event));
    }
}
