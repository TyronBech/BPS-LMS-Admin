<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;
use App\Notifications\BackupSucceeded;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class SendBackupSucceededNotification
{
    public function handle(BackupWasSuccessful $event): void
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !($admin instanceof Admin)) {
            return;
        }

        $userName = $admin->first_name . ' ' . $admin->last_name;

        // Send notification directly to the admin user
        Notification::send($admin, new BackupSucceeded($userName, $event));
    }
}
