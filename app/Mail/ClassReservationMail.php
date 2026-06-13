<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $reservation;
    private $emailMessage;
    private $status;
    private $remarks;
    private array $msg;
    public $logoData;
    public $defaultLogoPath;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $reservation, $emailMessage, $status, $remarks = null, array $msg = [])
    {
        $this->user = $user;
        $this->reservation = $reservation;
        $this->emailMessage = $emailMessage;
        $this->status = $status; // 'Approved' or 'Rejected'
        $this->remarks = $remarks;

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

        $subjectText = $status === 'Approved'
            ? '✅ Class Room Reservation Approved'
            : '❌ Class Room Reservation Rejected';

        $this->msg = array_replace([
            'brand_name'      => $brandName,
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => $subjectText,
            'title'           => $status === 'Approved' ? 'Class Reservation Approved' : 'Class Reservation Rejected',
            'greeting'        => "Dear {$displayName},",
            'approved_msg'    => 'Good news! Your class room reservation request has been approved.',
            'rejected_msg'    => 'Unfortunately, your class room reservation request has been rejected.',
            'reservation_date_label' => 'Reservation Date',
            'time_label'          => 'Time Slot',
            'purpose_label'       => 'Purpose',
            'remarks_label'       => 'Remarks',
            'cta_label'       => 'View My Reservations',
            'cta_url'         => rtrim(config('app.e_library_url'), '/') . '/web/reservations',
            'thanks'          => 'Thank you for using our library services.',
            'footer'          => 'If you have any questions, please contact the library staff. ℹ️',
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
            view: 'mail.classReservation',
            with: [
                'user'            => $this->user,
                'reservation'     => $this->reservation,
                'emailMessage'    => $this->emailMessage,
                'status'          => $this->status,
                'remarks'         => $this->remarks,
                'msg'             => $this->msg,
                'logoData'        => $this->logoData,
                'defaultLogoPath' => $this->defaultLogoPath,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
