<?php

namespace App\Http\Controllers\Maintenance;

use App\Enum\RolesEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Validator;
use App\Models\StagingUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountEmailMessage;
use App\Models\EmployeeDetail;
use App\Models\StudentDetail;
use Illuminate\Support\Facades\Auth;

class UsersMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $perStudentPage = $request->input('perStudentPage', 10);
        $perEmployeePage = $request->input('perEmployeePage', 10);
        $search = $request->input('search-users', '');
        $students = User::whereHas('students')
            ->with('students')
            ->orderBy('id', 'asc')
            ->paginate($perStudentPage)
            ->appends(['perPage' => $perStudentPage]);
        $employees = User::whereHas('employees')
            ->with('employees')
            ->orderBy('id', 'asc')
            ->paginate($perEmployeePage)
            ->appends(['perPage' => $perEmployeePage]);
        //dd($users->toArray());
        return view('maintenance.users.users', compact('students', 'employees', 'perStudentPage', 'perEmployeePage', 'search'));
    }
    public function view_student(Request $request)
    {
        $studentID = $request->input('id_number');
        $student = User::whereHas('students', function ($query) use ($studentID) {
            $query->where('id_number', $studentID);
        })
            ->with('students')
            ->first();
        return view('maintenance.users.view-student', compact('student'));
    }
    public function view_employee(Request $request)
    {
        $employeeID = $request->input('employee_id');
        $employee = User::whereHas('employees', function ($query) use ($employeeID) {
            $query->where('employee_id', $employeeID);
        })
            ->with('employees')
            ->first();
        return view('maintenance.users.view-employee', compact('employee'));
    }
    public function create_student()
    {
        return view('maintenance.users.create-student',);
    }
    public function create_employee()
    {
        $groups = UserGroup::where(DB::raw('lower(category)'), '!=', 'visitor')
            ->where(DB::raw('lower(category)'), '!=', 'student')
            ->pluck('category');
        return view('maintenance.users.create-employee', compact('groups'));
    }
    public function show(Request $request)
    {
        $perStudentPage  = $request->input('perStudentPage', 10);
        $perEmployeePage = $request->input('perEmployeePage', 10);
        $search          = strtolower($request->input('search-users', ''));

        // Common search filter closure
        $searchFilter = function ($query) use ($search) {
            $query->where(DB::raw('lower(first_name)'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(middle_name)'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(last_name)'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', "%{$search}%")
                ->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('rfid', 'like', "%{$search}%");
        };

        // Students query
        $students = User::whereHas('students')
            ->where(function ($q) use ($searchFilter) {
                $searchFilter($q);
            })
            ->orWhereHas('students', function ($q) use ($search) {
                $q->where('id_number', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate($perStudentPage, ['*'], 'students_page')
            ->appends([
                'perStudentPage' => $perStudentPage,
                'search-users'   => $search
            ]);

        // Employees query
        $employees = User::whereHas('employees')
            ->where(function ($q) use ($searchFilter) {
                $searchFilter($q);
            })
            ->orWhereHas('employees', function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate($perEmployeePage, ['*'], 'employees_page')
            ->appends([
                'perEmployeePage' => $perEmployeePage,
                'search-users'    => $search
            ]);

        return view('maintenance.users.users', compact('students', 'employees', 'perStudentPage', 'perEmployeePage', 'search'));
    }

    public function store_student(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'id_number'     => 'required|numeric|min:12|unique:' . (new StudentDetail())->getTable() . ',id_number',
            'level'         => 'required|numeric|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->hasFile('profile-image')) {
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $password = Str::password(8, true, true, false, false);
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'id_number'     => $request->input('id_number'),
                'level'         => $request->input('level'),
                'section'       => $request->input('section'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($password),
                'user_type'     => "student",
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage())->withInput();
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage())->withInput();
        }
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function store_employee(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10||regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'employee_id'   => 'required|string|max:50|unique:' . (new EmployeeDetail())->getTable() . ',employee_id',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->hasFile('profile-image')) {
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $password = Str::password(8, true, true, false, false);
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'employee_id'   => $request->input('employee_id'),
                'employee_role' => $request->input('employee_role'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($password),
                'user_type'     => "employee",
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage())->withInput();
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage())->withInput();
        }
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function edit_student(Request $request)
    {
        $user = null;
        try {
            $id = array_keys($request->all())[0];
            $user = User::with('students')->where('id', $id)->first();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit-student', compact('user'));
    }
    public function edit_employee(Request $request)
    {
        $user = null;
        try {
            $id = array_keys($request->all())[0];
            $user = User::with('employees', 'privileges')->where('id', $id)->first();
            $privileges = UserGroup::where(DB::raw('lower(user_type)'), '!=', 'visitor')
                ->where(DB::raw('lower(user_type)'), '!=', 'student')
                ->pluck('category');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit-employee', compact('user', 'privileges'));
    }
    public function update_student(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'id_number'     => 'required|numeric|min:12|unique:' . (new StudentDetail())->getTable() . ',id_number',
            'level'         => 'required|numeric|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->hasFile('profile-image')) {
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $student = User::with('students')->where('id', $request->input('id'))->first();
            if ($student) {
                $student->update([
                    'rfid'          => $request->input('rfid'),
                    'first_name'    => $request->input('first-name'),
                    'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                    'last_name'     => $request->input('last-name'),
                    'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                    'gender'        => $request->input('gender'),
                    'email'         => $request->input('email'),
                    'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                ]);
                $student->students()->update([
                    'id_number'     => $request->input('id_number'),
                    'level'         => $request->input('level'),
                    'section'       => $request->input('section'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    public function update_employee(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'employee_id'   => 'required|string|max:50|unique:' . (new EmployeeDetail())->getTable() . ',employee_id',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->hasFile('profile-image')) {
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        $privileges = UserGroup::where(DB::raw('lower(user_type)'), '!=', 'visitor')
            ->where(DB::raw('lower(user_type)'), '!=', 'student')->pluck('id', 'category')->toArray();
        if (!array_key_exists($request->input('employee_role'), $privileges)) {
            return redirect()->back()->with('toast-warning', 'User role is invalid')->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $employee = User::with('employees')->where('id', $request->input('id'))->first();
            $employee->update([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'email'         => $request->input('email'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'privilege_id'  => $privileges[$request->input('employee_role')],
            ]);
            $employee->employees()->update([
                'employee_id'   => $request->input('employee_id'),
                'employee_role' => $request->input('employee_role'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $id = $request->input('id');
            $user = User::findOrFail($id); // Throws ModelNotFoundException if not found

            if ($user->hasRole(RolesEnum::SUPER_ADMIN)) {
                DB::rollBack(); // Rollback transaction before redirecting
                return redirect()->back()->with('delete-error', 'Cannot delete a super admin user');
            } else if ($user->getRoleNames()) {
                $user->syncRoles([]);
            }

            $user->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'User not found.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'A database error occurred while deleting the user.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'An unexpected error occurred while deleting the user.');
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User deleted successfully');
    }
    public function bulk_delete_student(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('student_ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No students selected for deletion!');
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            User::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.users')->with('toast-success', 'Users deleted successfully');
    }
    public function bulk_delete_employee(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('employee_ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No employees selected for deletion!');
        }
        DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
        $user = User::whereIn('id', $ids)->get();
        if ($user->contains(function ($u) {
            return $u->hasRole(RolesEnum::SUPER_ADMIN);
        })) {
            return redirect()->back()->with('toast-warning', 'Cannot delete a super admin user');
        }
        DB::beginTransaction();
        try {
            if ($user->contains(function ($u) {
                return $u->getRoleNames();
            })) {
                $user->each(function ($u) {
                    $u->syncRoles([]);
                });
            }
            User::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.users')->with('toast-success', 'Users deleted successfully');
    }
    private function account_notification($user, $password)
    {
        Mail::to($user->email)->send(new AccountEmailMessage($user, $password));
    }
    private function extract_enums($table, $columnName)
    {
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        return $enumValues;
    }
    private function has_invalid_characters($name)
    {
        $pattern = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/';
        return !(bool) preg_match($pattern, $name);
    }
}
