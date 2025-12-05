<?php

namespace App\Mail;

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

        // Formal, emoji-friendly defaults
        $this->msg = array_replace([
            'brand_name'      => 'BPS Library Management System',
            'brand_logo_alt'  => 'BPS Logo',
            'subject'         => '🎓 Role Assignment Notification',
            'title'           => 'Role Update Notification 📩',
            'greeting'        => "Dear {$displayName},",
            'intro'           => 'We are pleased to inform you that your role within the BPS Library Management System has been updated.',
            'instruction'     => 'Please sign in to review your updated access and permissions.',
            'details_title'   => 'Assignment details 🧩',
            'email_label'     => 'Account email 📧',
            'role_label'      => 'Assigned role 🏷️',
            'cta_label'       => 'Sign in 🔓',
            'cta_url'         => env('APP_URL', route('login')) . '/login',
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
            from: new Address('bps@gmail.com', 'BPS Admin'),
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
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
