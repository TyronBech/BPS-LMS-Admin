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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsersMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perStudentPage     = $request->input('perStudentPage', 10);
        $perEmployeePage    = $request->input('perEmployeePage', 10);
        $perVisitorPage     = $request->input('perVisitorPage', 10);
        $search             = $request->input('search-users', '');

        Log::info('Users Maintenance: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'search_term' => $search,
            'per_student_page' => $perStudentPage,
            'per_employee_page' => $perEmployeePage,
            'per_visitor_page' => $perVisitorPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perStudentPage'    => 'nullable|integer|min:1|max:500',
            'perEmployeePage'   => 'nullable|integer|min:1|max:500',
            'perVisitorPage'    => 'nullable|integer|min:1|max:500',
            'search-users'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Users Maintenance: Invalid perPage parameter', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $students = User::whereHas('students')
            ->with('students')
            ->orderBy('created_at', 'desc')
            ->paginate($perStudentPage, ['*'], 'students_page')
            ->appends([
                'perStudentPage' => $perStudentPage,
                'search-users'   => $search,
            ]);

        $employees = User::whereHas('employees')
            ->with('employees')
            ->orderBy('created_at', 'desc')
            ->paginate($perEmployeePage, ['*'], 'employees_page')
            ->appends([
                'perEmployeePage' => $perEmployeePage,
                'search-users'    => $search,
            ]);

        $visitors = User::whereHas('visitors')
            ->with('visitors')
            ->orderBy('created_at', 'desc')
            ->paginate($perVisitorPage, ['*'], 'visitors_page')
            ->appends([
                'perVisitorPage' => $perVisitorPage,
                'search-users'   => $search,
            ]);

        return view('maintenance.users.users', compact('students', 'employees', 'visitors', 'perStudentPage', 'perEmployeePage', 'perVisitorPage', 'search'));
    }
    /**
     * View a student profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function view_student(Request $request)
    {
        $mimeType = null;
        $studentID = $request->input('id_number');

        Log::info('Users Maintenance: Viewing student profile', [
            'user_id' => Auth::guard('admin')->id(),
            'student_id_number' => $studentID,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $student = User::whereHas('students', function ($query) use ($studentID) {
            $query->where('id_number', $studentID);
        })
            ->with('students')
            ->first();
        if(!$student) {
            Log::warning('Users Maintenance: Student not found', ['student_id_number' => $studentID]);
            return redirect()->back()->with('toast-error', 'Student not found.')->withInput();
        }
        $base64Image = $student->profile_image;
        if (!empty($base64Image)) {
            $imageData = base64_decode($base64Image);

            // Detect MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
        }
        return view('maintenance.users.view-student', compact('student', 'mimeType'));
    }
    /**
     * View an employee profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function view_employee(Request $request)
    {
        $mimeType = null;
        $employeeID = $request->input('employee_id');

        Log::info('Users Maintenance: Viewing employee profile', [
            'user_id' => Auth::guard('admin')->id(),
            'employee_id' => $employeeID,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $employee = User::whereHas('employees', function ($query) use ($employeeID) {
            $query->where('employee_id', $employeeID);
        })
            ->with('employees')
            ->first();
        if(!$employee) {
            Log::warning('Users Maintenance: Employee not found', ['employee_id' => $employeeID]);
            return redirect()->back()->with('toast-error', 'Employee not found.')->withInput();
        }
        $base64Image = $employee->profile_image;
        if (!empty($base64Image)) {
            $imageData = base64_decode($base64Image);

            // Detect MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
        }
        return view('maintenance.users.view-employee', compact('employee', 'mimeType'));
    }
    /**
     * Display the form for creating a new student.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_student()
    {
        Log::info('Users Maintenance: Create student form accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);
        return view('maintenance.users.create-student');
    }
    /**
     * Display the form for creating a new employee.
     *
     * This function will return a view of the form for creating a new employee.
     * It will also fetch all the groups that are not 'visitor' or 'student' and
     * pass them to the view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_employee()
    {
        Log::info('Users Maintenance: Create employee form accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);
        $groups = UserGroup::where(DB::raw('lower(category)'), '!=', 'visitor')
            ->where(DB::raw('lower(category)'), '!=', 'student')
            ->pluck('category');
        return view('maintenance.users.create-employee', compact('groups'));
    }
    /**
     * Show the list of students, employees, and visitors.
     *
     * This function will return a view of the list of students, employees, and visitors.
     * It will also fetch the per page values for each and pass them to the view.
     * A search filter is also applied to the queries.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $perStudentPage     = $request->input('perStudentPage', 10);
        $perEmployeePage    = $request->input('perEmployeePage', 10);
        $perVisitorPage     = $request->input('perVisitorPage', 10);
        $search             = strtolower($request->input('search-users', ''));

        Log::info('Users Maintenance: Searching users', [
            'user_id' => Auth::guard('admin')->id(),
            'search_term' => $search,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perStudentPage'    => 'nullable|integer|min:1|max:500',
            'perEmployeePage'   => 'nullable|integer|min:1|max:500',
            'perVisitorPage'    => 'nullable|integer|min:1|max:500',
            'search-users'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Users Maintenance: Invalid search parameters', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

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
            ->where(function ($q) use ($searchFilter) { $searchFilter($q); })
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
            ->where(function ($q) use ($searchFilter) { $searchFilter($q); })
            ->orWhereHas('employees', function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate($perEmployeePage, ['*'], 'employees_page')
            ->appends([
                'perEmployeePage' => $perEmployeePage,
                'search-users'    => $search
            ]);

        // Visitors query
        $visitors = User::whereHas('visitors')
            ->where(function ($q) use ($searchFilter) { $searchFilter($q); })
            ->orWhereHas('visitors', function ($q) use ($search) {
                $q->where('school_org', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate($perVisitorPage, ['*'], 'visitors_page')
            ->appends([
                'perVisitorPage' => $perVisitorPage,
                'search-users'   => $search
            ]);

        return view('maintenance.users.users', compact('students', 'employees', 'visitors', 'perStudentPage', 'perEmployeePage', 'perVisitorPage', 'search'));
    }
    /**
     * Store a newly created student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_student(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $users = new User();

        Log::info('Users Maintenance: Attempting to create student', [
            'user_id' => Auth::guard('admin')->id(),
            'rfid' => $request->input('rfid'),
            'email' => $request->input('email'),
            'id_number' => $request->input('id_number'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10|regex:/^[0-9]+$/u',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'profile-image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'id_number'     => 'required|string|min:12|regex:/^[0-9]+$/u',
            'level'         => 'required|numeric|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ], [
            'email.unique' => 'The email has already been registered.',
        ]);
        if ($validator->fails()) {
            Log::warning('Users Maintenance: Student creation validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
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
            $password = Str::password(8, true, true, true, false);
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
            Log::error('Users Maintenance: Database error during student creation', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage())->withInput();
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Users Maintenance: Error calling DistributeStagingUsers', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage())->withInput();
        }
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
        return redirect()->route('maintenance.users')->with('toast-success', 'User added successfully');
    }
    /**
     * Store a newly created employee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     * @throws \Exception
     */
    public function store_employee(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $users = new User();

        Log::info('Users Maintenance: Attempting to create employee', [
            'user_id' => Auth::guard('admin')->id(),
            'rfid' => $request->input('rfid'),
            'email' => $request->input('email'),
            'employee_id' => $request->input('employee_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10|regex:/^[0-9]+$/u',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10||regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'profile-image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'employee_id'   => 'required|string|min:6|max:12|regex:/^[0-9]+$/u',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email|unique:' . $users->getTable() . ',email',
        ],
        [
            'email.unique' => 'The email has already been registered.',
        ]);
        if ($validator->fails()) {
            Log::warning('Users Maintenance: Employee creation validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
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
            $password = Str::password(8, true, true, true, false);
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
            Log::error('Users Maintenance: Database error during employee creation', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage())->withInput();
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Users Maintenance: Error calling DistributeStagingUsers', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage())->withInput();
        }
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
        return redirect()->route('maintenance.users')->with('toast-success', 'User added successfully');
    }
    /**
     * Edit student
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\QueryException
     */
    public function edit_student(Request $request)
    {
        $user = null;
        try {
            $id = $request->input('id');
            Log::info('Users Maintenance: Edit student form accessed', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $id,
                'timestamp' => now(),
            ]);
            $user = User::with('students')->where('id', $id)->first();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Users Maintenance: Error accessing edit student form', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        return view('maintenance.users.edit-student', compact('user'));
    }
    /**
     * Edit employee
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\QueryException
     */
    public function edit_employee(Request $request)
    {
        $user = null;
        try {
            $id = $request->input('id');
            Log::info('Users Maintenance: Edit employee form accessed', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $id,
                'timestamp' => now(),
            ]);
            $user = User::with('employees', 'privileges')->where('id', $id)->first();
            $privileges = UserGroup::where(DB::raw('lower(user_type)'), '!=', 'visitor')
                ->where(DB::raw('lower(user_type)'), '!=', 'student')
                ->pluck('category');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Users Maintenance: Error accessing edit employee form', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        return view('maintenance.users.edit-employee', compact('user', 'privileges'));
    }
    /**
     * Edit a visitor
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\QueryException
     */
    public function edit_visitor(Request $request)
    {
        $user = null;
        try {
            $id = $request->input('id');
            Log::info('Users Maintenance: Edit visitor form accessed', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $id,
                'timestamp' => now(),
            ]);
            $user = User::with('visitors')->where('id', $id)->first();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Users Maintenance: Error accessing edit visitor form', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        return view('maintenance.users.edit-visitor', compact('user'));
    }
    /**
     * Update student
     *
     * This function updates a student user given a request containing the required data.
     * The function first validates the request data, then updates the student user.
     * If the validation fails, the function redirects back with a toast warning and the validation error.
     * If the update fails, the function rolls back the database transaction and redirects back with a toast error and the exception message.
     * If the update succeeds, the function commits the database transaction and redirects back with a toast success message.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\QueryException
     */
    public function update_student(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $users = new User();

        Log::info('Users Maintenance: Attempting to update student', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'rfid' => $request->input('rfid'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10|regex:/^[0-9]+$/u',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'id_number'     => 'required|string|min:12|regex:/^[0-9]+$/u',
            'profile-image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'level'         => 'required|numeric|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email',
        ]);
        if ($validator->fails()) {
            Log::warning('Users Maintenance: Student update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $student = User::with('students')->where('id', $request->input('id'))->first();
            if ($student) {
                $profileImage = $student->profile_image;

                if ($request->hasFile('profile-image')) {
                    $image = $request->file('profile-image');
                    $imageContent = file_get_contents($image->getRealPath());
                    $profileImage = base64_encode($imageContent);
                }

                $studentData = [
                    'rfid'          => $request->input('rfid'),
                    'first_name'    => $request->input('first-name'),
                    'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                    'last_name'     => $request->input('last-name'),
                    'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                    'gender'        => $request->input('gender'),
                    'email'         => $request->input('email'),
                    'profile_image' => $profileImage,
                ];

                $student->update($studentData);
                $student->students()->update([
                    'id_number'     => $request->input('id_number'),
                    'level'         => $request->input('level'),
                    'section'       => $request->input('section'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Database error during student update', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Student updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    /**
     * Update an employee user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function update_employee(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $users = new User();

        Log::info('Users Maintenance: Attempting to update employee', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'rfid' => $request->input('rfid'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10|regex:/^[0-9]+$/u',
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'profile-image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'employee_id'   => 'required|string|min:6|max:12|regex:/^[0-9]+$/u',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email',
        ]);
        if ($validator->fails()) {
            Log::warning('Users Maintenance: Employee update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
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
            $profileImage = $employee?->profile_image;

            if ($request->hasFile('profile-image')) {
                $image = $request->file('profile-image');
                $imageContent = file_get_contents($image->getRealPath());
                $profileImage = base64_encode($imageContent);
            }

            $employeeData = [
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'email'         => $request->input('email'),
                'profile_image' => $profileImage,
                'privilege_id'  => $privileges[$request->input('employee_role')],
            ];

            $employee->update($employeeData);
            $employee->employees()->update([
                'employee_id'   => $request->input('employee_id'),
                'employee_role' => $request->input('employee_role'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Database error during employee update', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Employee updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'User updated successfully');
    }
    /**
     * Update a visitor user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function update_visitor(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $users = new User();

        Log::info('Users Maintenance: Attempting to update visitor', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'profile-image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'email'         => 'required|string|email',
            'school_org'   => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            Log::warning('Users Maintenance: Visitor update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $visitor = User::where('id', $request->input('id'))->first();
            $profileImage = $visitor?->profile_image;

            if ($request->hasFile('profile-image')) {
                $image = $request->file('profile-image');
                $imageContent = file_get_contents($image->getRealPath());
                $profileImage = base64_encode($imageContent);
            }

            $visitorData = [
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'email'         => $request->input('email'),
                'profile_image' => $profileImage,
            ];

            $visitor->update($visitorData);
            $visitor->visitors()->update([
                'school_org'   => $request->input('school_org'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Database error during visitor update', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Visitor updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'User updated successfully');
    }
    /**
     * Delete a user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Database\QueryException
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Log::warning('Users Maintenance: Attempting to delete user', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $id = $request->input('id');
            $user = User::findOrFail($id); // Throws ModelNotFoundException if not found

            if ($user->hasRole(RolesEnum::SUPER_ADMIN)) {
                Log::warning('Users Maintenance: Delete failed - Cannot delete super admin', [
                    'user_id' => Auth::guard('admin')->id(),
                    'target_user_id' => $id,
                    'timestamp' => now(),
                ]);
                DB::rollBack(); // Rollback transaction before redirecting
                return redirect()->back()->with('delete-error', 'Cannot delete a super admin user');
            } else if ($user->getRoleNames()) {
                $user->syncRoles([]);
            }

            $user->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Delete failed - User not found', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('delete-error', 'User not found.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Database error during deletion', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('delete-error', 'A database error occurred while deleting the user.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Unexpected error during deletion', [
                'user_id' => Auth::guard('admin')->id(),
                'target_user_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('delete-error', 'An unexpected error occurred while deleting the user.');
        }
        DB::commit();
        Log::info('Users Maintenance: User deleted successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'target_user_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'User deleted successfully');
    }
    /**
     * Bulk delete students.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Database\QueryException
     *
     * @return \Illuminate\Http\Response
     */
    public function bulk_delete_student(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('student_ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No students selected for deletion!')->withInput();
        }

        Log::warning('Users Maintenance: Attempting bulk delete students', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'ids' => $ids,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            User::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Bulk delete students failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Bulk delete students successful', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'Users deleted successfully');
    }
    /**
     * Bulk delete employees.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     *
     * @throws \Exception
     *
     * Bulk deletes employees with the given IDs. Checks if any of the employees
     * have the super admin role and returns an error if so. Otherwise, deletes
     * the employees and their roles.
     */
    public function bulk_delete_employee(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('employee_ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No employees selected for deletion!')->withInput();
        }

        Log::warning('Users Maintenance: Attempting bulk delete employees', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'ids' => $ids,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
        $user = User::whereIn('id', $ids)->get();
        if ($user->contains(function ($u) {
            return $u->hasRole(RolesEnum::SUPER_ADMIN);
        })) {
            Log::warning('Users Maintenance: Bulk delete employees failed - Contains super admin', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', 'Cannot delete a super admin user')->withInput();
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
            Log::error('Users Maintenance: Bulk delete employees failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Bulk delete employees successful', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'Users deleted successfully');
    }
    /**
     * Bulk deletes visitors with the given IDs.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     * @throws \Exception
     *
     * Bulk deletes visitors with the given IDs. Checks if any of the visitors
     * have the super admin role and returns an error if so. Otherwise, deletes
     * the visitors and their roles.
     */
    public function bulk_delete_visitor(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('visitor_ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No visitors selected for deletion!')->withInput();
        }

        Log::warning('Users Maintenance: Attempting bulk delete visitors', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'ids' => $ids,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            User::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Users Maintenance: Bulk delete visitors failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong!')->withInput();
        }
        DB::commit();
        Log::info('Users Maintenance: Bulk delete visitors successful', [
            'user_id' => Auth::guard('admin')->id(),
            'ids_count' => count($ids),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.users')->with('toast-success', 'Users deleted successfully');
    }
    /**
     * Sends an account notification email to the given user with the given password.
     *
     * @param \App\Models\User $user
     * @param string $password
     */
    private function account_notification($user, $password)
    {
        Log::info('Users Maintenance: Sending account notification email', [
            'recipient_email' => $user->email,
            'timestamp' => now(),
        ]);
        try{
            Mail::to($user->email)->send(new AccountEmailMessage($user, $password));
            Log::info('Users Maintenance: Account notification email sent', [
                'recipient_email' => $user->email,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Users Maintenance: Failed to send account notification email', [
                'recipient_email' => $user->email,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
        }
    }
    /**
     * Extracts the enum values from a given table and column name.
     *
     * @param string $table The name of the table to query.
     * @param string $columnName The name of the column to extract the enum values from.
     * @return array An array of enum values.
     */
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
}
