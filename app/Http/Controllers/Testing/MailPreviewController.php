<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Mail\AccountEmailMessage;
use App\Mail\BackupPasswordMail;
use App\Mail\BackupSuccessMail;
use App\Mail\ChangePasswordMail;
use App\Mail\ReservationMail;
use App\Mail\RoleEmailMessage;
use App\Mail\TwoFactorMail;
use Illuminate\Mail\Mailable;
use Illuminate\View\View;

class MailPreviewController extends Controller
{
    public function index(): View
    {
        $previews = collect($this->mailDefinitions())
            ->map(fn(array $definition, string $slug) => [
                'slug' => $slug,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'url' => route('testing.mail.preview', ['mail' => $slug]),
            ])
            ->values();

        return view('testing.mail-preview-index', compact('previews'));
    }

    public function show(string $mail): Mailable
    {
        $definition = $this->mailDefinitions()[$mail] ?? null;

        abort_if(!$definition, 404);

        return $definition['factory']();
    }

    private function mailDefinitions(): array
    {
        return [
            'account-email' => [
                'label' => 'Account Email',
                'description' => 'Preview the new account credentials email.',
                'factory' => fn() => new AccountEmailMessage($this->sampleUser(), 'TempPass123!'),
            ],
            'backup-password' => [
                'label' => 'Backup Password',
                'description' => 'Preview the encrypted backup password email.',
                'factory' => fn() => new BackupPasswordMail('Juan Dela Cruz', 'backup-secure-2026'),
            ],
            'backup-success' => [
                'label' => 'Backup Success',
                'description' => 'Preview the successful backup notification email.',
                'factory' => fn() => new BackupSuccessMail('Juan Dela Cruz', 'local'),
            ],
            'change-password' => [
                'label' => 'Change Password',
                'description' => 'Preview the password change confirmation email.',
                'factory' => fn() => new ChangePasswordMail($this->sampleUser()),
            ],
            'extension-approved' => [
                'label' => 'Extension Approved',
                'description' => 'Preview the approved book extension email.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your book extension request has been approved by the library. Your new due date is ' . now()->addDays(7)->format('M d, Y'),
                    'extended',
                    now()->addDays(7),
                    'Good',
                    '0.00',
                    'No Penalty'
                ),
            ],
            'reservation-available' => [
                'label' => 'Reservation Ready for Pickup',
                'description' => 'Preview the approved reservation email when book is available.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your book reservation request for "' . $this->sampleBook()->title . '" has been approved. The book is now available for pickup until ' . now()->addDays(3)->format('M d, Y') . '.',
                    'reserved_available',
                    now()->addDays(3),
                    'Good',
                    '0.00',
                    'No Penalty',
                    [
                        'subject' => '✅ Book Reservation Ready for Pickup',
                        'title' => 'Book Reservation Approved',
                        'greeting' => "Dear Juan Dela Cruz,",
                        'approved_msg' => 'Good news! Your book reservation request has been approved and is ready for pickup.',
                    ]
                ),
            ],
            'reservation-queued' => [
                'label' => 'Reservation Placed in Queue',
                'description' => 'Preview the approved reservation email when book is currently borrowed.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your book reservation request for "' . $this->sampleBook()->title . '" has been approved. The book is currently borrowed by another user. You will be notified once it becomes available for pickup.',
                    'reserved_queued',
                    now(),
                    'Good',
                    '0.00',
                    'No Penalty',
                    [
                        'subject' => '📌 Book Reservation Approved & Placed in Queue',
                        'title' => 'Book Reservation Approved (In Queue)',
                        'greeting' => "Dear Juan Dela Cruz,",
                        'approved_msg' => 'Good news! Your book reservation request has been approved. You are now in line for this book.',
                    ]
                ),
            ],
            'reservation-rejected' => [
                'label' => 'Reservation / Extension Rejected',
                'description' => 'Preview the rejected reservation or extension email.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your request could not be approved at this time. Reason: Material is reserved for another transaction. Please contact the library staff for more information.',
                    'rejected',
                    now()->addDays(2),
                    'Good',
                    '0.00',
                    'Pending'
                ),
            ],
            'role-update' => [
                'label' => 'Role Update',
                'description' => 'Preview the assigned role notification email.',
                'factory' => fn() => new RoleEmailMessage($this->sampleUser(), 'Library Staff'),
            ],
            'two-factor' => [
                'label' => 'Two Factor',
                'description' => 'Preview the one-time password login verification email.',
                'factory' => fn() => new TwoFactorMail($this->sampleUser(), '482913'),
            ],
        ];
    }

    private function sampleUser(): object
    {
        return (object) [
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'email' => 'juan.delacruz@example.com',
        ];
    }

    private function sampleBook(): object
    {
        return (object) [
            'title' => 'Introduction to Library Systems',
            'author' => 'Maria Santos',
            'accession' => 'ACC-2026-001',
        ];
    }
}
