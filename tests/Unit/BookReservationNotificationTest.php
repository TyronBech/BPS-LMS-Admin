<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationMail;

class BookReservationNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(PermissionsEnum::RESERVATION_APPROVALS->value);
    }

    /**
     * Test reservation approval when book is available.
     */
    public function test_approve_reservation_when_book_available(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'availability_status' => 'Available',
            'condition_status' => 'Good',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'Pending',
            'transaction_type' => 'Reserved',
            'remarks' => 'Initial request',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.approve-extension', $transaction->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Reservation request for ' . $book->title . ' has been approved. Status is set to "Available for pick up".');

        $transaction->refresh();
        $this->assertEquals('Available for pick up', $transaction->status);
        $this->assertNotNull($transaction->pickup_deadline);

        $book->refresh();
        $this->assertEquals('Reserved', $book->availability_status);

        Mail::assertSent(ReservationMail::class, function ($mail) use ($user) {
            $this->assertEquals('reserved_available', $this->getPrivatePropertyValue($mail, 'transactionType'));
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Test reservation approval when book is borrowed/unavailable.
     */
    public function test_approve_reservation_when_book_unavailable(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'availability_status' => 'Borrowed',
            'condition_status' => 'Good',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'Pending',
            'transaction_type' => 'Reserved',
            'remarks' => 'Initial request',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.approve-extension', $transaction->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Reservation request for ' . $book->title . ' has been approved. The user is now in line for this book.');

        $transaction->refresh();
        $this->assertEquals('Reserved', $transaction->status);
        $this->assertNull($transaction->pickup_deadline);

        $book->refresh();
        $this->assertEquals('Borrowed', $book->availability_status); // Remains borrowed

        Mail::assertSent(ReservationMail::class, function ($mail) use ($user) {
            $this->assertEquals('reserved_queued', $this->getPrivatePropertyValue($mail, 'transactionType'));
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Helper to read private/protected property of an object.
     */
    private function getPrivatePropertyValue($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
