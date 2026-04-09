<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $otp;
    private array $msg;
    public $logoData;
    public $defaultLogoPath;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $otp, array $msg = [])
    {
        $this->user = $user;
        $this->otp = $otp;

        $settings = UISetting::first() ?? new UISetting();

        // Build a clean display name
        $first  = trim((string)($user->first_name ?? ''));
        $middle = trim((string)($user->middle_name ?? ''));
        $last   = trim((string)($user->last_name ?? ''));
        $displayName = trim($first . ' ' . ($middle ? $middle . ' ' : '') . $last);

        // Get logo from settings or fallback to default
        $this->logoData = $settings->org_logo ? base64_decode($settings->org_logo) : null;
        $this->defaultLogoPath = public_path('img/OwlQuery.png');
        $brandName = trim(($settings->org_initial ?? '') . ' ' . config('app.name'));

        $this->msg = array_replace([
            'brand_name'      => $brandName,
            // 'brand_logo' removed
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => '🔐 Two-Factor Authentication Code - ' . $brandName,
            'title'           => 'Two-Factor Authentication',
            'greeting'        => "Dear {$displayName},",
            'intro'           => 'Someone is attempting to access your account. If this was you, use the code below to complete your login.',
            'otp_label'       => 'Your Verification Code',
            'otp_expiry'      => '⏱️ This code expires in 10 minutes',
            'security_notice' => 'If you did not attempt to log in, please ignore this email and ensure your password is secure. Never share this code with anyone, including library staff.',
            'security_tips'   => [
                'This code can only be used once',
                'Do not share this code with anyone',
                'Our team will never ask for this code',
                'Code expires automatically after 10 minutes',
            ],
            'help_text'       => 'If you didn\'t receive the code or it has expired, you can request a new one by returning to the login page and clicking "Resend Code."',
            'cta_label'       => 'Go to Login Page',
            'cta_url'         => config('app.e_library_url'),
            'contact_email'   => 'owlquery.tech@gmail.com',
            'contact_phone'   => '(02) 8252-9613',
            'contact_hours'   => 'Monday-Friday, 8:00 AM - 5:00 PM',
            'thanks'          => 'Thank you for keeping your account secure.',
            'footer'          => 'This is an automated security message. Please do not reply to this email.',
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
            view: 'mail.twoFactor',
            with: [
                'user' => $this->user,
                'otp'  => $this->otp,
                'msg'  => $this->msg,
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
