<?php

namespace App\Notifications;

use App\Mail\BackupSuccessMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupSucceeded extends Notification
{
    use Queueable;

    public function __construct(
        public string $userName,
        public ?BackupWasSuccessful $event = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $disk = $this->event?->backupDestination?->diskName() ?? 'backups';
        return (new BackupSuccessMail($this->userName, $disk))->to($notifiable);
    }
}
