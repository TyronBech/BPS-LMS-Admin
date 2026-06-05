<?php

namespace Tests\Unit;

use App\Enum\PermissionsEnum;
use App\Enum\RolesEnum;
use App\Http\Controllers\Import\UserImageImportController;
use App\Jobs\ProcessUserImageImport;
use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserImageImportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test the upload endpoint categorizes images properly.
     */
    public function test_upload_categorizes_images_properly(): void
    {
        // 1. Setup authenticated admin with correct role/permissions
        $admin = User::factory()->create([
            'email' => 'admin-import-test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $role = Role::firstOrCreate([
            'name' => RolesEnum::SUPER_ADMIN->value,
            'guard_name' => 'admin'
        ]);
        $admin->assignRole($role);
        Auth::guard('admin')->login($admin);

        // 2. Setup database users
        $studentUser = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $studentDetail = StudentDetail::factory()->create([
            'user_id' => $studentUser->id,
            'id_number' => 'STUD-12345',
        ]);

        $employeeUser = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
        $employeeDetail = EmployeeDetail::factory()->create([
            'user_id' => $employeeUser->id,
            'employee_id' => 'EMP-54321',
        ]);

        // 3. Prepare dummy uploaded files (matched, unmatched, oversized)
        $matchedStudentFile = UploadedFile::fake()->image('STUD-12345.jpg', 100, 100);
        $matchedEmployeeFile = UploadedFile::fake()->image('EMP-54321.png', 100, 100);
        $unmatchedFile = UploadedFile::fake()->image('UNKNOWN-999.jpeg', 100, 100);
        $oversizedFile = UploadedFile::fake()->create('STUD-12345-BIG.jpg', 6 * 1024); // 6MB, larger than 5MB limit

        // 4. Post upload request
        $response = $this->post(route('import.upload-user-images'), [
            'images' => [
                $matchedStudentFile,
                $matchedEmployeeFile,
                $unmatchedFile,
                $oversizedFile,
            ]
        ]);

        $response->assertStatus(200); // Renders the view with status 200 OK
        $response->assertSessionHas('user_image_import_matched');
        $response->assertSessionHas('user_image_import_unmatched');
        $response->assertSessionHas('user_image_import_oversized');

        $matched = session('user_image_import_matched');
        $unmatched = session('user_image_import_unmatched');
        $oversized = session('user_image_import_oversized');

        // Assert matched files
        $this->assertCount(2, $matched);
        $this->assertEquals('STUD-12345', $matched[0]['id']);
        $this->assertEquals('Student', $matched[0]['user_type']);
        $this->assertEquals('EMP-54321', $matched[1]['id']);
        $this->assertEquals('Employee', $matched[1]['user_type']);

        // Assert unmatched files
        $this->assertCount(1, $unmatched);
        $this->assertEquals('UNKNOWN-999', $unmatched[0]['id']);

        // Assert oversized files
        $this->assertCount(1, $oversized);
        $this->assertEquals('STUD-12345-BIG', $oversized[0]['id']);
    }

    /**
     * Test the store endpoint dispatches the ProcessUserImageImport job.
     */
    public function test_store_dispatches_job(): void
    {
        Queue::fake();

        // 1. Setup authenticated admin
        $admin = User::factory()->create();
        $role = Role::firstOrCreate([
            'name' => RolesEnum::SUPER_ADMIN->value,
            'guard_name' => 'admin'
        ]);
        $admin->assignRole($role);
        Auth::guard('admin')->login($admin);

        // Mock matched files in session
        $matchedMock = [
            [
                'path' => Storage::path('temp_user_images/STUD-12345.jpg'),
                'filename' => 'STUD-12345.jpg',
                'id' => 'STUD-12345',
                'size' => 1024,
                'size_text' => '1.00 KB',
                'user_id' => 10,
                'user_name' => 'John Doe',
                'user_type' => 'Student',
            ]
        ];
        session(['user_image_import_matched' => $matchedMock]);

        // Post store request
        $response = $this->postJson(route('import.store-user-images'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify job dispatched
        Queue::assertPushed(ProcessUserImageImport::class, function ($job) use ($matchedMock, $admin) {
            return count($job->matchedFiles) === 1 && $job->matchedFiles[0]['id'] === 'STUD-12345';
        });

        // Session keys should be cleared
        $this->assertNull(session('user_image_import_matched'));
    }

    /**
     * Test ProcessUserImageImport job processes and updates student profile images and cleans up files.
     */
    public function test_job_processes_student_image_and_unlinks_temp_file(): void
    {
        // 1. Setup database users
        $studentUser = User::factory()->create(['profile_image' => null]);
        $studentDetail = StudentDetail::factory()->create([
            'user_id' => $studentUser->id,
            'id_number' => 'STUD-5555',
        ]);

        // 2. Put a real dummy file in local disk
        $dummyContent = 'FakeImageContentBytes';
        $tempPath = 'temp_user_images/STUD-5555.jpg';
        Storage::put($tempPath, $dummyContent);
        $absolutePath = Storage::path($tempPath);

        $this->assertFileExists($absolutePath);

        // Create ImportProgress record
        $progress = ImportProgress::create([
            'type' => 'user_images',
            'status' => 'pending',
            'initiated_by' => User::factory()->create()->id,
            'total_rows' => 1,
        ]);

        $matchedFiles = [
            [
                'path' => $absolutePath,
                'filename' => 'STUD-5555.jpg',
                'id' => 'STUD-5555',
                'size' => strlen($dummyContent),
                'size_text' => '21 B',
            ]
        ];

        // 3. Instantiate and run job handle method
        $job = new ProcessUserImageImport($matchedFiles, $progress->id, $progress->initiated_by);
        $job->handle();

        // 4. Verify results
        $progress->refresh();
        $this->assertEquals('completed', $progress->status);
        $this->assertEquals(1, $progress->processed_rows);
        $this->assertEquals(1, $progress->updated_count);
        $this->assertEquals(0, $progress->new_count); // new_count behaves as skippedCount in this job

        // Verify base64 image update
        $studentUser->refresh();
        $this->assertEquals(base64_encode($dummyContent), $studentUser->profile_image);

        // Verify file was unlinked from storage
        $this->assertFileDoesNotExist($absolutePath);
    }
}
