<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail($notifiable): MailMessage
    {
        $disk = $this->event?->backupDestination?->diskName() ?? 'backups';

        return (new MailMessage)
            ->subject('Backup Generated: ' . 'Database Backup Completed')
            ->view('mail.backupGenerateSuccess', [
                'app' => config('app.name'),
                'disk' => $disk,
                'date' => now(),
                'userName' => $this->userName,
                'url' => config('app.url'),
            ]);
    }
}
