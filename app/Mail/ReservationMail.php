<?php

namespace App\Mail;

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
    private $emailMessage; // Renamed from $message
    private $transactionType;
    private $dueDate;
    private $conditionStatus;
    private $penaltyTotal;
    private $penaltyStatus;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $book, $emailMessage, $transactionType, $dueDate, $conditionStatus, $penaltyTotal, $penaltyStatus)
    {
        $this->user = $user;
        $this->book = $book;
        $this->emailMessage = $emailMessage;
        $this->transactionType = $transactionType;
        $this->dueDate = $dueDate;
        $this->conditionStatus = $conditionStatus;
        $this->penaltyTotal = $penaltyTotal;
        $this->penaltyStatus = $penaltyStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->transactionType === 'extended'
            ? 'Book Extension Request Approved'
            : 'Book Extension Request Rejected';

        return new Envelope(
            subject: $subject,
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
                'emailMessage'      => $this->emailMessage, // Renamed from 'message'
                'transactionType'   => $this->transactionType,
                'dueDate'           => $this->dueDate,
                'conditionStatus'   => $this->conditionStatus,
                'penaltyTotal'      => $this->penaltyTotal,
                'penaltyStatus'     => $this->penaltyStatus,
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
