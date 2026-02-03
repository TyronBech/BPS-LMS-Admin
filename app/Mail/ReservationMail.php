<?php

namespace App\Mail;

use App\Models\UISetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $book;
    private $emailMessage;
    private $transactionType;
    private $dueDate;
    private $conditionStatus;
    private $penaltyTotal;
    private $penaltyStatus;
    private array $msg;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $book, $emailMessage, $transactionType, $dueDate, $conditionStatus, $penaltyTotal, $penaltyStatus, array $msg = [])
    {
        $this->user = $user;
        $this->book = $book;
        $this->emailMessage = $emailMessage;
        $this->transactionType = $transactionType;
        $this->dueDate = $dueDate;
        $this->conditionStatus = $conditionStatus;
        $this->penaltyTotal = $penaltyTotal;
        $this->penaltyStatus = $penaltyStatus;

        $settings = UISetting::first() ?? new UISetting();

        // Build a clean display name
        $first  = trim((string)($user->first_name ?? ''));
        $middle = trim((string)($user->middle_name ?? ''));
        $last   = trim((string)($user->last_name ?? ''));
        $displayName = trim($first . ' ' . ($middle ? $middle . ' ' : '') . $last);

        // Get logo from settings or fallback to default
        $logo = $settings->getOrgLogoBase64Attribute() ?? asset('img/OwlQuery.png');

        $subjectText = $transactionType === 'extended'
            ? '✅ Book Extension Request Approved'
            : '❌ Book Extension Request Rejected';

        $this->msg = array_replace([
            'brand_name'      => ($settings->org_initial ?? '') . ' Library Management System',
            'brand_logo'      => $logo,
            'brand_logo_alt'  => ($settings->org_initial ?? '') . ' Logo',
            'subject'         => $subjectText,
            'title'           => $transactionType === 'extended' ? 'Extension Request Approved' : 'Extension Request Rejected',
            'greeting'        => "Dear {$displayName},",
            'approved_msg'    => 'Good news! Your extension request has been approved.',
            'rejected_msg'    => 'Unfortunately, your extension request has been rejected.',
            'book_title_label'     => 'Book Title',
            'new_due_date_label'   => 'New Due Date',
            'condition_label'      => 'Book Condition',
            'penalty_label'        => 'Penalty',
            'penalty_status_label' => 'Penalty Status',
            'reason_label'         => 'Reason',
            'cta_label'       => 'View My Reservations',
            'cta_url'         => env('E_LIBRARY_URL') . '/web/reservations',
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
            view: 'mail.reservationExtension',
            with: [
                'user'              => $this->user,
                'book'              => $this->book,
                'emailMessage'      => $this->emailMessage,
                'transactionType'   => $this->transactionType,
                'dueDate'           => $this->dueDate,
                'conditionStatus'   => $this->conditionStatus,
                'penaltyTotal'      => $this->penaltyTotal,
                'penaltyStatus'     => $this->penaltyStatus,
                'msg'               => $this->msg,
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
