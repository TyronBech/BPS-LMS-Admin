<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $disk;
    public $logoData;
    public $defaultLogoPath;
    public $msg;
    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $disk)
    {
        $this->userName = $userName;
        $this->disk = $disk;
        $this->settings = UISetting::first() ?? new UISetting();

        $this->logoData = $this->settings->org_logo ? base64_decode($this->settings->org_logo) : null;
        $this->defaultLogoPath = public_path('img/OwlQuery.png');

        $this->msg = [
            'org_initial'     => $this->settings->org_initial ?? '',
            'brand_name'      => ($this->settings->org_initial ?? '') . ' Library Management System',
            // 'brand_logo' removed to avoid base64 clipping
            'brand_logo_alt'  => ($this->settings->org_initial ?? '') . ' Logo',
            'subject'         => '✅ Backup Generated: System Backup Completed',
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
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->msg['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.backupGenerateSuccess',
            with: [
                'app' => config('app.name'),
                'disk' => $this->disk,
                'date' => now(),
                'userName' => $this->userName,
                'url' => config('app.url'),
                'msg' => $this->msg,
                'settings' => $this->settings,
                'logoData' => $this->logoData,
                'defaultLogoPath' => $this->defaultLogoPath,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
