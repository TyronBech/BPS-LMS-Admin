<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ChangePasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    private array $msg;

    /**
     * Create a new message instance.
     */
    public function __construct(private $user, array $msg = [])
    {
        $this->user = $user;

        // Build a clean display name (handles optional middle name)
        $first  = trim((string)($user->first_name ?? ''));
        $middle = trim((string)($user->middle_name ?? ''));
        $last   = trim((string)($user->last_name ?? ''));
        $displayName = trim($first . ' ' . ($middle ? $middle . ' ' : '') . $last);
        $settings = UISetting::first() ?? new UISetting();

        // Get logo from settings or fallback to default
        $logo = $settings->org_logo 
            ? 'data:image/png;base64,' . $settings->org_logo 
            : asset('img/OwlQuery.png');

        // Message-driven copy (formal + emojis)
        $this->msg = array_replace([
            'org_initial'     => $settings->org_initial ?? '',
            'brand_name'      => ($settings->org_initial ?? '') . ' Library Management System',
            'brand_logo'      => $logo,
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => '🔐 Password Change Confirmation',
            'title'           => 'Password Updated Successfully ✅',
            'greeting'        => "Dear {$displayName},",
            'intro'           => 'This is to confirm that the password for your ' . ($settings->org_initial ?? '') . ' Library account has been updated successfully.',
            'details_title'   => 'Change details',
            'email_label'     => 'Account email',
            'security_note'   => 'If you did not make this change, please reset your password immediately and contact support. 🛡️',
            'cta_label'       => 'Reset your password',
            'cta_url'         => env('APP_URL') . '/admin/profile',
            'thanks'          => 'Thank you for helping us keep your account secure.',
            'footer'          => 'This is an automated message. Please do not reply. ℹ️',
        ], $msg);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->msg['subject'],
            from: new Address(env('MAIL_FROM_ADDRESS', 'bps@gmail.com'), ($this->msg['org_initial'] ?? '') . ' Admin'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.changePassMsg',
            with: [
                'user' => $this->user,
                'msg'  => $this->msg,
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
