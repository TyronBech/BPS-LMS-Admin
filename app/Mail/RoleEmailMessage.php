<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class RoleEmailMessage extends Mailable
{
    use Queueable, SerializesModels;

    private array $msg;
    public $logoData;
    public $defaultLogoPath;

    /**
     * Create a new message instance.
     */
    public function __construct(private $user, private $role, array $msg = [])
    {
        $this->user = $user;
        $this->role = $role;

        // Build a clean display name
        $first  = trim((string)($user->first_name ?? ''));
        $middle = trim((string)($user->middle_name ?? ''));
        $last   = trim((string)($user->last_name ?? ''));
        $displayName = trim($first . ' ' . ($middle ? $middle . ' ' : '') . $last);
        $settings = UISetting::first() ?? new UISetting();

        // Get logo from settings or fallback to default
        $this->logoData = $settings->org_logo ? base64_decode($settings->org_logo) : null;
        $this->defaultLogoPath = public_path('img/OwlQuery.png');
        $brandName = trim(($settings->org_initial ?? '') . ' ' . config('app.name'));

        // Formal, emoji-friendly defaults
        $this->msg = array_replace([
            'org_initial'     => $settings->org_initial ?? '',
            'brand_name'      => $brandName,
            // 'brand_logo' removed
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => '🎓 Role Assignment Notification',
            'title'           => 'Role Update Notification 📩',
            'greeting'        => "Dear {$displayName},",
            'intro'           => 'We are pleased to inform you that your role within the ' . $brandName . ' has been updated.',
            'instruction'     => 'Please sign in to review your updated access and permissions.',
            'details_title'   => 'Assignment details 🧩',
            'email_label'     => 'Account email 📧',
            'role_label'      => 'Assigned role 🏷️',
            'cta_label'       => 'Sign in 🔓',
            'cta_url'         => rtrim(config('app.url', route('login')), '/') . '/login',
            'thanks'          => 'Thank you for your continued support of our library services.',
            'footer'          => 'If you believe this change was made in error, please contact support. ℹ️',
        ], $msg);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->msg['subject'],
            from: new Address(config('mail.from.address', 'bps@gmail.com'), ($this->msg['org_initial'] ?? '') . ' Admin'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.roleMsg',
            with: [
                'user' => $this->user,
                'role' => $this->role,
                'msg'  => $this->msg,
                'logoData' => $this->logoData,
                'defaultLogoPath' => $this->defaultLogoPath,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
