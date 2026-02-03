<?php

namespace App\Notifications;

use App\Models\UISetting;
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
        $settings = UISetting::first() ?? new UISetting();

        // Get logo from settings or fallback to default
        $logo = $settings->org_logo
            ? 'data:image/png;base64,' . $settings->org_logo
            : asset('img/OwlQuery.png');

        $msg = [
            'org_initial'     => $settings->org_initial ?? '',
            'brand_name'      => ($settings->org_initial ?? '') . ' Library Management System',
            'brand_logo'      => $logo,
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => '✅ Backup Generated: Database Backup Completed',
            'title'           => 'Backup Completed',
            'greeting'        => "Dear {$this->userName},",
            'details_title'   => 'Backup details',
            'app_label'       => 'Application',
            'disk_label'      => 'Disk',
            'date_label'      => 'Generated at',
            'cta_label'       => 'Open ' . config('app.name'),
            'cta_url'         => config('app.url'),
            'thanks'          => 'Thank you.',
            'footer'          => 'This is an automated message. Please do not reply.',
        ];

        return (new MailMessage)
            ->subject($msg['subject'])
            ->view('mail.backupGenerateSuccess', [
                'app' => config('app.name'),
                'disk' => $disk,
                'date' => now(),
                'userName' => $this->userName,
                'url' => config('app.url'),
                'msg' => $msg,
                'settings' => $settings,
            ]);
    }
}
