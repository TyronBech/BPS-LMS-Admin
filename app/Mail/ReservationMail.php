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
    private $message;
    private $transactionType;
    private $dueDate;
    private $conditionStatus;
    private $penaltyTotal;
    private $penaltyStatus;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $book, $message, $transactionType, $dueDate, $conditionStatus, $penaltyTotal, $penaltyStatus)
    {
        $this->user = $user;
        $this->book = $book;
        $this->message = $message;
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
        return new Envelope(
            subject: 'Extension of Reservation Notification',
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
                'message'           => $this->message,
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
