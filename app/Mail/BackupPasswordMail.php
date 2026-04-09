<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The username.
     *
     * @var string
     */
    private $username;

    /**
     * The backup password.
     *
     * @var string
     */
    private $password;

    private array $msg;

    public $logoData;
    public $defaultLogoPath;

    /**
     * Create a new message instance.
     */
    public function __construct($username, $password, array $msg = [])
    {
        $this->username = $username;
        $this->password = $password;

        $settings = UISetting::first() ?? new UISetting();

        // Get logo from settings or fallback to default
        $this->logoData = $settings->org_logo ? base64_decode($settings->org_logo) : null;
        $this->defaultLogoPath = public_path('img/OwlQuery.png');
        $brandName = trim(($settings->org_initial ?? '') . ' ' . config('app.name'));

        $this->msg = array_replace([
            'brand_name'      => $brandName,
            // 'brand_logo' removed to prevent clipping
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => $brandName . ' - Your Backup Password',
            'title'           => 'Database Backup Password 🔒',
            'greeting'        => "Dear {$username},",
            'intro'           => 'A new database backup has been created and secured with encryption. Use the password below to open the backup archive when restoring or reviewing the backup.',
            'details_title'   => 'Backup details',
            'username_label'  => 'Requested by',
            'password_label'  => 'Backup password',
            'cta_label'       => 'Open the System',
            'cta_url'         => config('app.url'),
            'security_note'   => 'Keep this password secure. Do not share it over chat or with untrusted parties. ' . ($settings->org_initial ?? '') . ' staff will never ask you to disclose this password.',
            'footer'          => 'This is an automated message. Please do not reply.',
        ], $msg);
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
            view: 'mail.backupPassword',
            with: [
                'username' => $this->username,
                'password' => $this->password,
                'msg'      => $this->msg,
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
