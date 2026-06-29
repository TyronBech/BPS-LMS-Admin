<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BkNonCirculation;
use App\Models\User;
use App\Models\StudentDetail;
use App\Models\EmployeeDetail;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NonCirculationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creating a non-circulation entry and check model relationships.
     */
    public function test_create_non_circulation_entry_relationships(): void
    {
        $user = User::factory()->create();

        $student = StudentDetail::create([
            'user_id' => $user->id,
            'id_number' => 'STUD-NC-123',
            'level' => 'Grade 9',
            'section' => 'St. Luke',
        ]);

        $nonCirculation = BkNonCirculation::create([
            'student_id' => $student->id,
            'subject' => 'Science Book',
            'borrowed_at' => now(),
        ]);

        $this->assertDatabaseHas('bk_non_circulations', [
            'id' => $nonCirculation->id,
            'subject' => 'Science Book',
        ]);

        $this->assertEquals($student->id, $nonCirculation->student->id);
        $this->assertEquals($user->id, $nonCirculation->student->users->id);
    }

    /**
     * Test accessing the non-circulation index route as an authorized user.
     */
    public function test_authorized_user_can_access_non_circulation_report(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_NON_CIRCULATION_REPORTS->value);

        $response = $this->actingAs($admin, 'admin')->get(route('report.non-circulation'));

        $response->assertOk();
    }

    /**
     * Test validation on storing non-circulation entries.
     */
    public function test_store_non_circulation_validation(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value);

        $response = $this->actingAs($admin, 'admin')->post(route('report.non-circulation-store'), [
            'modal_user_type' => 'student',
            // Missing student_id, subject
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning');
    }

    /**
     * Test successfully storing a non-circulation entry.
     */
    public function test_store_non_circulation_success(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value);

        $studentUser = User::factory()->create();
        StudentDetail::create([
            'user_id' => $studentUser->id,
            'id_number' => 'STUD-NC-999',
            'level' => 'Grade 8',
            'section' => 'B',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('report.non-circulation-store'), [
            'modal_user_type' => 'student',
            'student_id' => $studentUser->id,
            'subject' => 'History Reference',
            'borrowed_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bk_non_circulations', [
            'subject' => 'History Reference',
        ]);
    }

    /**
     * Test successfully soft deleting a non-circulation entry.
     */
    public function test_delete_non_circulation_entry_success(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value);

        $studentUser = User::factory()->create();
        $student = StudentDetail::create([
            'user_id' => $studentUser->id,
            'id_number' => 'STUD-NC-888',
            'level' => 'Grade 12',
            'section' => 'C',
        ]);

        $nonCirculation = BkNonCirculation::create([
            'student_id' => $student->id,
            'subject' => 'Physics Encyclopedia',
            'borrowed_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('report.non-circulation-delete'), [
            'id' => $nonCirculation->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Non-Circulation entry deleted successfully.');
        
        // Assert that it's soft deleted
        $this->assertSoftDeleted('bk_non_circulations', [
            'id' => $nonCirculation->id,
        ]);
    }

    /**
     * Test delete validation fails if ID does not exist.
     */
    public function test_delete_non_circulation_validation_fails(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value);

        $response = $this->actingAs($admin, 'admin')->delete(route('report.non-circulation-delete'), [
            'id' => 999999, // non-existent
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning');
    }
}
