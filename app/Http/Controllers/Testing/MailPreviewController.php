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
            'reservation-approved' => [
                'label' => 'Reservation Approved',
                'description' => 'Preview the approved reservation extension email.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your extension request has been approved. Please return the book on or before the new due date shown below.',
                    'extended',
                    now()->addDays(7),
                    'Good',
                    '0.00',
                    'Paid'
                ),
            ],
            'reservation-rejected' => [
                'label' => 'Reservation Rejected',
                'description' => 'Preview the rejected reservation extension email.',
                'factory' => fn() => new ReservationMail(
                    $this->sampleUser(),
                    $this->sampleBook(),
                    'Your extension request could not be approved at this time. Please return the book on the original due date or coordinate with the library staff.',
                    'rejected',
                    now()->addDays(2),
                    'Good',
                    '25.00',
                    'Unpaid'
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
