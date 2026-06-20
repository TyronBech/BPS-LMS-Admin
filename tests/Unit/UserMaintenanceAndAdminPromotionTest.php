<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\StudentDetail;
use App\Models\EmployeeDetail;
use App\Models\StagingUser;
use App\Enum\PermissionsEnum;
use App\Enum\RolesEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserMaintenanceAndAdminPromotionTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private UserGroup $studentGroup;
    private UserGroup $employeeGroup;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles/permissions exist
        $superAdminRole = Role::firstOrCreate([
            'name' => RolesEnum::SUPER_ADMIN->value,
            'guard_name' => 'admin',
        ]);

        $superAdminRole->givePermissionTo(PermissionsEnum::VIEW_USERS_MAINTENANCE->value);
        $superAdminRole->givePermissionTo(PermissionsEnum::ADD_USERS->value);
        $superAdminRole->givePermissionTo(PermissionsEnum::EDIT_USERS->value);
        $superAdminRole->givePermissionTo(PermissionsEnum::MODIFY_ADMIN->value);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin-test-' . uniqid() . '@example.com',
            'rfid' => 'adminrfid123',
        ]);
        $this->adminUser->assignRole($superAdminRole);

        // Setup user groups
        $this->studentGroup = UserGroup::firstOrCreate([
            'user_type' => 'student',
            'category' => 'Regular Student',
        ], [
            'max_book_allowed' => 3,
            'duration_type' => 'standard',
            'renewal_limit' => 2,
        ]);

        $this->employeeGroup = UserGroup::firstOrCreate([
            'user_type' => 'employee',
            'category' => 'Teacher',
        ], [
            'max_book_allowed' => 5,
            'duration_type' => 'standard',
            'renewal_limit' => 3,
        ]);
    }

    /**
     * Test creating a student with a null RFID.
     */
    public function test_store_student_with_null_rfid(): void
    {
        $email = 'student-' . uniqid() . '@example.com';
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('maintenance.store-student'), [
                'rfid' => null,
                'first-name' => 'John',
                'last-name' => 'Doe',
                'gender' => 'Male',
                'id_number' => 'STUD' . rand(10000000, 99999999),
                'level' => 'Grade 10',
                'section' => 'A',
                'email' => $email,
            ]);

        $response->assertRedirect(route('maintenance.users'));
        $response->assertSessionHas('toast-success', 'User added successfully');

        $this->assertDatabaseHas('usr_users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'rfid' => null,
        ]);
    }

    /**
     * Test creating an employee with a null RFID.
     */
    public function test_store_employee_with_null_rfid(): void
    {
        $email = 'employee-' . uniqid() . '@example.com';
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('maintenance.store-employee'), [
                'rfid' => null,
                'first-name' => 'Jane',
                'last-name' => 'Smith',
                'gender' => 'Female',
                'employee_id' => 'EMP-' . rand(100, 999),
                'employee_role' => 'Teacher',
                'email' => $email,
            ]);

        $response->assertRedirect(route('maintenance.users'));
        $response->assertSessionHas('toast-success', 'User added successfully');

        $this->assertDatabaseHas('usr_users', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $email,
            'rfid' => null,
        ]);
    }

    /**
     * Test updating a student to have a null RFID.
     */
    public function test_update_student_with_null_rfid(): void
    {
        $student = User::factory()->create([
            'privilege_id' => $this->studentGroup->id,
            'rfid' => 'oldstudentrfid',
        ]);
        
        // Ensure student detail exists with valid id_number length
        if (!$student->students) {
            StudentDetail::factory()->create([
                'user_id' => $student->id,
            ]);
            $student->load('students');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
            ->from(route('maintenance.users'))
            ->put(route('maintenance.update-student'), [
                'id' => $student->id,
                'rfid' => null,
                'first-name' => $student->first_name,
                'last-name' => $student->last_name,
                'gender' => $student->gender,
                'id_number' => $student->students->id_number,
                'level' => 'Grade 11',
                'section' => 'B',
                'email' => $student->email,
            ]);

        $response->assertRedirect(route('maintenance.users'));
        $response->assertSessionHas('toast-success', 'User updated successfully');

        $student->refresh();
        $this->assertNull($student->rfid);
    }

    /**
     * Test updating an employee to have a null RFID.
     */
    public function test_update_employee_with_null_rfid(): void
    {
        $employee = User::factory()->create([
            'privilege_id' => $this->employeeGroup->id,
            'rfid' => 'oldemployeerfid',
        ]);

        // Ensure employee detail exists with valid employee_id length
        if (!$employee->employees) {
            EmployeeDetail::factory()->create([
                'user_id' => $employee->id,
                'employee_id' => 'EMP-' . rand(100, 999),
                'employee_role' => 'Teacher',
            ]);
            $employee->load('employees');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
            ->from(route('maintenance.users'))
            ->put(route('maintenance.update-employee'), [
                'id' => $employee->id,
                'rfid' => null,
                'first-name' => $employee->first_name,
                'last-name' => $employee->last_name,
                'gender' => $employee->gender,
                'employee_id' => $employee->employees->employee_id,
                'employee_role' => 'Teacher',
                'email' => $employee->email,
            ]);

        $response->assertRedirect(route('maintenance.users'));
        $response->assertSessionHas('toast-success', 'User updated successfully');

        $employee->refresh();
        $this->assertNull($employee->rfid);
    }

    /**
     * Test promoting a user with a null RFID to admin by using their user database ID.
     */
    public function test_promote_user_to_admin_with_null_rfid_using_id(): void
    {
        // Create student user with no RFID
        $student = User::factory()->create([
            'privilege_id' => $this->studentGroup->id,
            'rfid' => null,
        ]);
        
        // Ensure student detail exists
        if (!$student->students) {
            StudentDetail::factory()->create([
                'user_id' => $student->id,
            ]);
            $student->load('students');
        }

        // Create the admin roles we want to assign
        $role = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'admin',
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('maintenance.store-admin'), [
                'adminID' => $student->id, // Send database ID instead of RFID
                'role' => 'Admin',
            ]);

        $response->assertRedirect(route('maintenance.admins'));
        $response->assertSessionHas('toast-success', 'Admin created successfully');

        $student->refresh();
        $this->assertTrue($student->hasRole('Admin'));
    }
}
