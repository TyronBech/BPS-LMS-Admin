<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Printing;
use App\Models\User;
use App\Models\StudentDetail;
use App\Models\EmployeeDetail;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PrintingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creating a printing entry and check model relationships.
     */
    public function test_create_printing_entry_relationships(): void
    {
        // Create user
        $user = User::factory()->create();

        // Create student detail
        $student = StudentDetail::create([
            'user_id' => $user->id,
            'id_number' => 'STUD-TEST-123',
            'level' => 'Grade 10',
            'section' => 'St. Jude',
        ]);

        // Create printing entry
        $printing = Printing::create([
            'student_id' => $student->id,
            'type' => 'print',
            'topic' => 'Math Assignment',
            'pages' => 5,
            'printed_at' => now(),
        ]);

        $this->assertDatabaseHas('printing', [
            'id' => $printing->id,
            'topic' => 'Math Assignment',
            'pages' => 5,
        ]);

        $this->assertEquals($student->id, $printing->student->id);
        $this->assertEquals($user->id, $printing->student->users->id);
    }

    /**
     * Test accessing the printing index route as an authorized user.
     */
    public function test_authorized_user_can_access_printing_report(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_PRINTING_REPORTS->value);

        $response = $this->actingAs($admin, 'admin')->get(route('report.printing'));

        $response->assertOk();
    }

    /**
     * Test validation on storing printing entries.
     */
    public function test_store_printing_validation(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_PRINTING_ENTRY->value);

        // Send request with missing fields
        $response = $this->actingAs($admin, 'admin')->post(route('report.printing-store'), [
            'modal_user_type' => 'student',
            // Missing student_id, type, topic, pages
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning');
    }

    /**
     * Test successfully storing a printing entry.
     */
    public function test_store_printing_success(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_PRINTING_ENTRY->value);

        $studentUser = User::factory()->create();
        StudentDetail::create([
            'user_id' => $studentUser->id,
            'id_number' => 'STUD-TEST-999',
            'level' => 'Grade 11',
            'section' => 'A',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('report.printing-store'), [
            'modal_user_type' => 'student',
            'student_id' => $studentUser->id,
            'type' => 'print',
            'topic' => 'English Essay',
            'pages' => 3,
            'amount' => 15.00,
            'printed_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('printing', [
            'topic' => 'English Essay',
            'amount' => 15.00,
        ]);
    }
}
