<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountEmailMessage extends Mailable
{
    use Queueable, SerializesModels;

    private array $msg;
    public $logoData;
    public $defaultLogoPath;

    /**
     * Create a new message instance.
     */
    public function __construct(private $user, private string $password, array $msg = [])
    {
        $this->user = $user;
        $this->password = $password;

        $settings = UISetting::first() ?? new UISetting();

        // Build a clean display name (handles optional middle name)
        $first  = trim((string)($user->first_name ?? ''));
        $middle = trim((string)($user->middle_name ?? ''));
        $last   = trim((string)($user->last_name ?? ''));
        $displayName = trim($first . ' ' . ($middle ? $middle . ' ' : '') . $last);

        // Get logo from settings or fallback to default
        $this->logoData = $settings->org_logo ? base64_decode($settings->org_logo) : null;
        $this->defaultLogoPath = public_path('img/OwlQuery.png');

        $this->msg = array_replace([
            // UI/brand text now message-driven
            'brand_name'     => ($settings->org_initial ?? '') . ' Library Management System',
            // 'brand_logo' removed to prevent clipping
            'brand_logo_alt' => ($settings->org_initial ?? '') . ' Logo',

            // Formal copy with emojis
            'subject'        => '📚 Your ' . ($settings->org_initial ?? '') . ' Library Account Details',
            'title'          => ($settings->org_initial ?? '') . ' Library Account 📩',
            'greeting'       => "Dear {$displayName},",
            'intro'          => 'We are pleased to inform you that your ' . ($settings->org_initial ?? '') . ' Library Management System account has been successfully created.',
            'instruction'    => 'To begin, please sign in using the credentials provided below.',
            'details_title'  => 'Account credentials 🔐',
            'email_label'    => 'Email',
            'password_label' => 'Temporary password',
            'reminder'       => 'For your security, please change your password after your first login. 🛡️',
            'thanks'         => 'Thank you for being part of the ' . ($settings->org_initial ?? '') . ' learning community.',
            'cta_label'      => 'Access your account 🔓',
            'cta_url'        => env('E_LIBRARY_URL') . '/web/login',
            'footer'         => 'If you did not request or expect this message, please disregard this email or contact support. ℹ️',
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
            view: 'mail.accountNotif',
            with: [
                'user'     => $this->user,
                'password' => $this->password,
                'msg'      => $this->msg,
                'logoData' => $this->logoData,
                'defaultLogoPath' => $this->defaultLogoPath,
            ],
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
