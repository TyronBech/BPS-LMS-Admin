<?php

namespace Tests\Unit;

use App\Jobs\ProcessEmployeeImport;
use App\Models\ImportProgress;
use App\Models\StagingUser;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\EmployeeDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class EmployeeImportPrivilegeAutomationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test that importing an employee with a new role automatically creates that role in the privileges table with unlimited privilege.
     */
    public function test_import_creates_new_role_with_unlimited_privilege(): void
    {
        // Clean up any leftovers
        UserGroup::where('category', 'Dean of Science')->forceDelete();
        User::where('email', 'unique.dean@example.com')->forceDelete();
        User::where('rfid', '987654321012')->forceDelete();
        EmployeeDetail::where('employee_id', 'EMP-UNIQUE-DEAN')->forceDelete();

        // 1. Create a dummy spreadsheet with a new role
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Populate rows up to row 18 (which is index 17) to satisfy the starting offset
        for ($r = 1; $r <= 18; $r++) {
            $sheet->setCellValue("A{$r}", "Header Row {$r}");
        }

        // Add the employee row on row 19
        $sheet->setCellValue("B19", "987654321012"); // RFID
        $sheet->setCellValue("C19", "Doe, John");  // Full Name
        $sheet->setCellValue("D19", "");            // Suffix
        $sheet->setCellValue("E19", "Male");        // Gender
        $sheet->setCellValue("F19", "unique.dean@example.com"); // Email
        $sheet->setCellValue("G19", "EMP-UNIQUE-DEAN");   // Employee ID
        $sheet->setCellValue("H19", "Dean of Science"); // New Employee Role

        $tempPath = 'temp_imports/test_employee_import.xlsx';
        $fullPath = Storage::disk('local')->path($tempPath);

        // Ensure directory exists and write the spreadsheet
        @mkdir(dirname($fullPath), 0777, true);
        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        // 2. Setup progress tracker and user who initiated it
        $initiator = User::factory()->create();
        $progress = ImportProgress::create([
            'type'         => 'employees',
            'status'       => 'pending',
            'initiated_by' => $initiator->id,
            'total_rows'   => 1,
        ]);

        // 3. Ensure the privilege doesn't exist initially
        $this->assertFalse(
            UserGroup::where('user_type', 'employee')
                ->where('category', 'Dean of Science')
                ->exists(),
            "The privilege category should not exist before import."
        );

        // 4. Run the import job
        $job = new ProcessEmployeeImport($tempPath, $progress->id, $progress->initiated_by);
        $job->handle();

        // 5. Verify the privilege category was created with unlimited privilege
        $privilege = UserGroup::where('user_type', 'employee')
            ->where('category', 'Dean of Science')
            ->first();

        $this->assertNotNull($privilege, "The privilege category should have been created.");
        $this->assertEquals('unlimited', $privilege->duration_type);
        $this->assertEquals(0, $privilege->max_book_allowed);
        $this->assertEquals(0, $privilege->renewal_limit);

        // 6. Verify that staging user was created and distributed/processed correctly
        $stagingUser = StagingUser::where('employee_role', 'Dean of Science')->first();
        // Since DistributeStagingUsers() procedure runs at the end of job, the staging users are cleared,
        // and the user should now exist in the permanent tables.
        $this->assertNull($stagingUser, "Staging users should have been cleared after running DistributeStagingUsers.");

        // Verify permanent user exists
        $user = User::where('email', 'unique.dean@example.com')->first();
        $this->assertNotNull($user, "The user should have been imported successfully.");
        $this->assertEquals($privilege->id, $user->privilege_id);

        $employeeDetail = $user->employees;
        $this->assertNotNull($employeeDetail, "Employee details should exist.");
        $this->assertEquals('EMP-UNIQUE-DEAN', $employeeDetail->employee_id);
        $this->assertEquals('Dean of Science', $employeeDetail->employee_role);
    }
}
