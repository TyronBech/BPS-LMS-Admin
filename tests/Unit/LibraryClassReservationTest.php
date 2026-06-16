<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\LibraryClassReservation;
use App\Models\User;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClassReservationMail;

class LibraryClassReservationTest extends TestCase
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
     * Test successful approval with no overlap.
     */
    public function test_approve_reservation_success(): void
    {
        $user = User::factory()->create();
        $reservation = LibraryClassReservation::create([
            'user_id' => $user->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'Pending',
            'purpose' => 'Study Group',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.class-reservations.approve', $reservation->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Library class reservation has been approved.');

        $reservation->refresh();
        $this->assertEquals('Approved', $reservation->status);
        $this->assertEquals($this->admin->id, $reservation->approved_by);

        Mail::assertSent(ClassReservationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Test approving a reservation fails if there is an already approved overlapping reservation.
     */
    public function test_approve_fails_if_overlapping_approved_exists(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Already approved reservation from 09:00 to 10:00
        LibraryClassReservation::create([
            'user_id' => $user1->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'Approved',
            'purpose' => 'Class A',
        ]);

        // Pending overlapping reservation from 09:30 to 10:30
        $pendingOverlapping = LibraryClassReservation::create([
            'user_id' => $user2->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '09:30:00',
            'end_time' => '10:30:00',
            'status' => 'Pending',
            'purpose' => 'Class B',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.class-reservations.approve', $pendingOverlapping->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning', 'Cannot approve this reservation because an overlapping reservation has already been approved.');

        $pendingOverlapping->refresh();
        $this->assertEquals('Pending', $pendingOverlapping->status);
    }

    /**
     * Test approving a reservation automatically cancels pending overlapping reservations.
     */
    public function test_approve_cancels_overlapping_pending_reservations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // 1. Pending reservation to be approved: 13:00 to 14:30
        $toApprove = LibraryClassReservation::create([
            'user_id' => $user1->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '13:00:00',
            'end_time' => '14:30:00',
            'status' => 'Pending',
            'purpose' => 'Main Class',
        ]);

        // 2. Conflicting pending reservation: 14:00 to 15:00 (overlaps)
        $conflict1 = LibraryClassReservation::create([
            'user_id' => $user2->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'Pending',
            'purpose' => 'Conflict 1',
        ]);

        // 3. Non-conflicting pending reservation: 15:00 to 16:00 (no overlap)
        $nonConflict = LibraryClassReservation::create([
            'user_id' => $user3->id,
            'reservation_date' => '2026-07-01',
            'start_time' => '15:00:00',
            'end_time' => '16:00:00',
            'status' => 'Pending',
            'purpose' => 'Non Conflict',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.class-reservations.approve', $toApprove->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Library class reservation has been approved.');

        $toApprove->refresh();
        $conflict1->refresh();
        $nonConflict->refresh();

        $this->assertEquals('Approved', $toApprove->status);
        $this->assertEquals('Cancelled', $conflict1->status);
        $this->assertStringContainsString('CANCELLED automatically due to overlapping approved reservation', $conflict1->remarks);
        $this->assertEquals('Pending', $nonConflict->status);

        Mail::assertSent(ClassReservationMail::class, function ($mail) use ($user1) {
            return $mail->hasTo($user1->email);
        });

        Mail::assertSent(ClassReservationMail::class, function ($mail) use ($user2) {
            return $mail->hasTo($user2->email);
        });

        Mail::assertNotSent(ClassReservationMail::class, function ($mail) use ($user3) {
            return $mail->hasTo($user3->email);
        });
    }
}
