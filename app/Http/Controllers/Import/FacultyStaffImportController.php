<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\StagingUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountEmailMessage;
use App\Models\EmployeeDetail;
use App\Models\UserGroup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacultyStaffImportController extends Controller
{
    /**
     * Faculty/Staff Import: Index page
     *
     * This function handles the index page of the Faculty/Staff Import
     * feature. It logs the user's access and clears the session data if
     * the user did not come from a pagination link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::info('Faculty/Staff Import: Index page accessed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            Log::debug('Faculty/Staff Import: Clearing session data', [
                'user_id' => Auth::id(),
                'action' => 'session_clear',
                'cleared_keys' => ['new_employee_data', 'existing_employee_data'],
            ]);

            $request->session()->forget('new_employee_data');
            $request->session()->forget('existing_employee_data');
        }

        $showTable = false;
        return view('import.employees.index', compact('showTable'));
    }

    /**
     * Stores the submitted data to session and validates the data.
     * The function handles the importation of new and existing employees.
     * It also sends account notification emails to the staged users.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::info('Faculty/Staff Import: Store process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $newEmployeesFromSession = $request->session()->get('new_employee_data', []);
        $existingEmployeesFromSession = $request->session()->get('existing_employee_data', []);

        Log::debug('Faculty/Staff Import: Retrieved session data', [
            'new_employees_count' => count($newEmployeesFromSession),
            'existing_employees_count' => count($existingEmployeesFromSession),
            'user_id' => Auth::id(),
        ]);

        // Merge last submitted data
        $submittedNew = $request->input('new_employees', []);
        foreach ($submittedNew as $index => $employee) {
            if (isset($newEmployeesFromSession[$index])) {
                $newEmployeesFromSession[$index] = array_merge($newEmployeesFromSession[$index], $employee);
            }
        }

        $submittedExisting = $request->input('existing_employees', []);
        foreach ($submittedExisting as $index => $employee) {
            if (isset($existingEmployeesFromSession[$index])) {
                $existingEmployeesFromSession[$index] = array_merge($existingEmployeesFromSession[$index], $employee);
            }
        }

        $dataArray              = array_merge($newEmployeesFromSession ?? array(), $existingEmployeesFromSession ?? array());
        
        Log::info('Faculty/Staff Import: Total employees to process', [
            'total_count' => count($dataArray),
            'user_id' => Auth::id(),
        ]);

        $errors                 = null;
        $staged_users           = array();
        $newFacultiesCount      = 0;
        $existingFacultiesCount = 0;
        $users                  = new User();

        DB::beginTransaction();
        Log::info('Faculty/Staff Import: Database transaction started', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        foreach ($dataArray as $index => $item) {
            // skip empty rows
            if (empty(array_filter($item))) {
                Log::debug('Faculty/Staff Import: Skipping empty row', [
                    'row_index' => $index,
                    'user_id' => Auth::id(),
                ]);
                continue;
            }

            Log::debug('Faculty/Staff Import: Validating employee data', [
                'row_index' => $index,
                'employee_name' => ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''),
                'employee_id' => $item['employee_id'] ?? 'N/A',
                'email' => $item['email'] ?? 'N/A',
                'employee_role' => $item['employee_role'] ?? 'N/A',
                'user_id' => Auth::id(),
            ]);

            $validator = Validator::make($item, [
                'rfid'          => 'nullable|string|min:10|regex:/^[0-9]+$/u',
                'first_name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'middle_name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'last_name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
                'gender'        => 'required|string|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
                'email'         => 'required|string|email|max:255',
                'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
                'employee_id'   => 'required|string|min:6|max:12|regex:/^[0-9]+$/u',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for faculty/staff: ' . $item['first_name'] . ' ' . $item['last_name'];

                Log::error('Faculty/Staff Import: Validation failed', [
                    'row_index' => $index,
                    'employee_name' => $item['first_name'] . ' ' . $item['last_name'],
                    'error_message' => $validator->errors()->first(),
                    'failed_fields' => $validator->errors()->keys(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
            }

            try {
                $password = Str::password(8, true, true, true, false);
                $existingEmployee = User::wherehas('employees', function ($query) use ($item) {
                    $query->where('employee_id', $item['employee_id']);
                })->with('employees')->first();

                if ($existingEmployee) {
                    Log::info('Faculty/Staff Import: Existing employee found', [
                        'employee_id' => $existingEmployee->id,
                        'employee_name' => $existingEmployee->full_name,
                        'employee_number' => $item['employee_id'],
                        'user_id' => Auth::id(),
                    ]);

                    if (
                        $existingEmployee->rfid                         == $item['rfid']
                        && $existingEmployee->first_name                == $item['first_name']
                        && $existingEmployee->middle_name               == $item['middle_name']
                        && $existingEmployee->last_name                 == $item['last_name']
                        && $existingEmployee->suffix                    == $item['suffix']
                        && $existingEmployee->gender                    == $item['gender']
                        && $existingEmployee->email                     == $item['email']
                        && $existingEmployee->employees->employee_role  == $item['employee_role']
                        && $existingEmployee->employees->employee_id    == $item['employee_id']
                    ) {
                        Log::debug('Faculty/Staff Import: No changes detected, skipping update', [
                            'employee_id' => $existingEmployee->id,
                            'employee_number' => $item['employee_id'],
                            'user_id' => Auth::id(),
                        ]);
                        continue;
                    }

                    $oldData = [
                        'rfid' => $existingEmployee->rfid,
                        'first_name' => $existingEmployee->first_name,
                        'middle_name' => $existingEmployee->middle_name,
                        'last_name' => $existingEmployee->last_name,
                        'suffix' => $existingEmployee->suffix,
                        'gender' => $existingEmployee->gender,
                        'email' => $existingEmployee->email,
                        'employee_role' => $existingEmployee->employees->employee_role,
                        'employee_id' => $existingEmployee->employees->employee_id,
                    ];

                    $existingEmployee->update([
                        'first_name'    => $item['first_name'],
                        'middle_name'   => $item['middle_name'],
                        'last_name'     => $item['last_name'],
                        'suffix'        => $item['suffix'],
                        'gender'        => $item['gender'],
                        'email'         => $item['email'],
                    ]);

                    $existingEmployee->employees()->update([
                        'employee_role' => $item['employee_role'],
                        'employee_id'   => $item['employee_id'],
                    ]);

                    Log::info('Faculty/Staff Import: Employee updated successfully', [
                        'employee_id' => $existingEmployee->id,
                        'employee_number' => $item['employee_id'],
                        'old_data' => $oldData,
                        'new_data' => $item,
                        'modified_by' => Auth::id(),
                        'modified_by_name' => Auth::user()->full_name,
                        'timestamp' => now(),
                    ]);

                    $existingFacultiesCount++;
                } else {
                    Log::debug('Faculty/Staff Import: Checking for duplicate email/RFID', [
                        'email' => $item['email'],
                        'rfid' => $item['rfid'],
                        'user_id' => Auth::id(),
                    ]);

                    if (StagingUser::where('email', $item['email'])->exists()) {
                        DB::rollBack();
                        $errors = "Email already exists for user: " . $item['first_name'] . " " . $item['last_name'];

                        Log::error('Faculty/Staff Import: Duplicate email detected', [
                            'email' => $item['email'],
                            'employee_name' => $item['first_name'] . ' ' . $item['last_name'],
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
                    } else if (StagingUser::where('rfid', $item['rfid'])->exists()) {
                        DB::rollBack();
                        $errors = "RFID already exists for user: " . $item['first_name'] . " " . $item['last_name'];

                        Log::error('Faculty/Staff Import: Duplicate RFID detected', [
                            'rfid' => $item['rfid'],
                            'employee_name' => $item['first_name'] . ' ' . $item['last_name'],
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
                    }

                    Log::info('Faculty/Staff Import: Creating new employee in staging', [
                        'employee_name' => $item['first_name'] . ' ' . $item['last_name'],
                        'email' => $item['email'],
                        'employee_id' => $item['employee_id'],
                        'employee_role' => $item['employee_role'],
                        'created_by' => Auth::id(),
                        'created_by_name' => Auth::user()->full_name,
                        'timestamp' => now(),
                    ]);

                    StagingUser::create([
                        'rfid'          => $item['rfid'],
                        'first_name'    => $item['first_name'],
                        'middle_name'   => $item['middle_name'],
                        'last_name'     => $item['last_name'],
                        'suffix'        => $item['suffix'],
                        'gender'        => $item['gender'],
                        'email'         => $item['email'],
                        'password'      => Hash::make($password),
                        'employee_id'   => $item['employee_id'],
                        'employee_role' => $item['employee_role'],
                        'user_type'     => 'employee',
                    ]);

                    $staged_users[] = [
                        'email'     => $item['email'],
                        'password'  => $password,
                    ];

                    Log::debug('Faculty/Staff Import: Employee staged successfully', [
                        'email' => $item['email'],
                        'user_id' => Auth::id(),
                    ]);

                    $newFacultiesCount++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if ($e->getCode() == 23000) {
                    $errors = "Duplicate ID found for user: " . $item['first_name'] . " " . $item['last_name'];
                } else if ($e->getCode() == "HY000") {
                    $errors = "An error occurred while saving users: Wrong format of excel file";
                } else {
                    $errors = "An error occurred while saving users: " . $e->getMessage();
                }

                Log::error('Faculty/Staff Import: Database error occurred', [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'employee_name' => $item['first_name'] . ' ' . $item['last_name'],
                    'sql_state' => $e->errorInfo[0] ?? 'N/A',
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
            }
        }

        DB::commit();
        Log::info('Faculty/Staff Import: Database transaction committed', [
            'new_employees' => $newFacultiesCount,
            'updated_employees' => $existingFacultiesCount,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        try {
            Log::info('Faculty/Staff Import: Executing stored procedure to distribute staged users', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            DB::statement('CALL DistributeStagingUsers()');

            Log::info('Faculty/Staff Import: Stored procedure executed successfully', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Faculty/Staff Import: Stored procedure execution failed', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }

        Log::info('Faculty/Staff Import: Sending account notification emails', [
            'email_count' => count($staged_users),
            'user_id' => Auth::id(),
        ]);

        foreach ($staged_users as $user) {
            $employee = User::where('email', $user['email'])->first();
            if ($employee == null) {
                Log::warning('Faculty/Staff Import: Employee not found for email notification', [
                    'email' => $user['email'],
                    'user_id' => Auth::id(),
                ]);
                continue;
            }

            Log::debug('Faculty/Staff Import: Sending email to employee', [
                'employee_id' => $employee->id,
                'employee_email' => $user['email'],
                'user_id' => Auth::id(),
            ]);

            $this->account_notification($employee, $user['password']);

            Log::info('Faculty/Staff Import: Email sent successfully', [
                'employee_id' => $employee->id,
                'employee_email' => $user['email'],
                'sent_by' => Auth::id(),
                'timestamp' => now(),
            ]);
        }

        $request->session()->forget('new_employee_data');
        $request->session()->forget('existing_employee_data');

        Log::info('Faculty/Staff Import: Process completed successfully', [
            'new_employees_count' => $newFacultiesCount,
            'updated_employees_count' => $existingFacultiesCount,
            'emails_sent' => count($staged_users),
            'completed_by' => Auth::id(),
            'completed_by_name' => Auth::user()->full_name,
            'timestamp' => now(),
        ]);

        return redirect()->route('import.import-faculties-staffs')->with('toast-success', 'Faculties & Staffs imported successfully: ' . $newFacultiesCount . ' added & ' . $existingFacultiesCount . ' updated');
    }

    /**
     * Handles the upload of faculties and staffs Excel file and stores the data in session.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        Log::info('Faculty/Staff Import: Upload process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'has_file' => $request->hasFile('file'),
            'timestamp' => now(),
        ]);

        try {
            $newSessionData = $request->session()->get('new_employee_data', []);
            $existingSessionData = $request->session()->get('existing_employee_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
                Log::debug('Faculty/Staff Import: Processing pagination request', [
                    'user_id' => Auth::id(),
                    'new_employees_in_session' => count($newSessionData),
                    'existing_employees_in_session' => count($existingSessionData),
                ]);

                // POST request for pagination, merge edits
                $submittedNew = $request->input('new_employees', []);
                foreach ($submittedNew as $index => $employee) {
                    if (isset($newSessionData[$index])) {
                        $newSessionData[$index] = array_merge($newSessionData[$index], $employee);
                    }
                }
                $request->session()->put('new_employee_data', $newSessionData);

                $submittedExisting = $request->input('existing_employees', []);
                foreach ($submittedExisting as $index => $employee) {
                    if (isset($existingSessionData[$index])) {
                        $existingSessionData[$index] = array_merge($existingSessionData[$index], $employee);
                    }
                }
                $request->session()->put('existing_employee_data', $existingSessionData);

                $newData = $newSessionData;
                $existingData = $existingSessionData;

            } else if ($request->hasFile('file')) {
                $file = $request->file('file');

                Log::info('Faculty/Staff Import: Processing uploaded file', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                // Initial file upload
                $reader = new ReaderXlsx();
                $spreadsheet = $reader->load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                Log::debug('Faculty/Staff Import: Excel file loaded', [
                    'total_rows' => count($rows),
                    'total_columns' => count($rows[0] ?? []),
                    'user_id' => Auth::id(),
                ]);

                $newData = [];
                $existingData = [];

                if ($rows[0][0] == null) {
                    Log::error('Faculty/Staff Import: Empty Excel file', [
                        'file_name' => $file->getClientOriginalName(),
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-faculties-staffs')->with('toast-error', "Excel file is empty.");
                } else if (count($rows[0]) > 11 || count($rows[0]) < 11) {
                    Log::error('Faculty/Staff Import: Invalid number of columns', [
                        'file_name' => $file->getClientOriginalName(),
                        'expected_columns' => 11,
                        'actual_columns' => count($rows[0]),
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-faculties-staffs')->with('toast-error', "An error occurred while saving faculties & staffs: Wrong number of columns.");
                }

                for ($i = 19; $i < count($rows); $i++) {
                    if (empty(array_filter(array_slice($rows[$i], 1, 7)))) {
                        Log::debug('Faculty/Staff Import: Skipping empty row in Excel', [
                            'row_number' => $i + 1,
                            'user_id' => Auth::id(),
                        ]);
                        continue;
                    }

                    $fullName = $this->extractNameParts($rows[$i][2] ?? '');
                    if (empty($fullName['first_name']) || empty($fullName['last_name'])) {
                        Log::error('Faculty/Staff Import: Invalid name format in Excel', [
                            'row_number' => $i + 1,
                            'full_name' => $rows[$i][2] ?? '',
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-faculties-staffs')->with('toast-error', "Invalid format in row " . ($i + 1) . ". Please ensure that the 'Full Name' field are correctly filled.");
                    }

                    $temp = [
                        'rfid'          => $rows[$i][1],
                        'first_name'    => $fullName['first_name'],
                        'middle_name'   => $fullName['middle_name'],
                        'last_name'     => $fullName['last_name'],
                        'suffix'        => $rows[$i][3],
                        'gender'        => $rows[$i][4],
                        'email'         => $rows[$i][5],
                        'employee_id'   => $rows[$i][6],
                        'employee_role' => $rows[$i][7],
                    ];

                    if (EmployeeDetail::where('employee_id', $temp['employee_id'])->exists()) {
                        Log::debug('Faculty/Staff Import: Existing employee detected from Excel', [
                            'row_number' => $i + 1,
                            'employee_id' => $temp['employee_id'],
                            'employee_name' => $temp['first_name'] . ' ' . $temp['last_name'],
                            'user_id' => Auth::id(),
                        ]);
                        $existingData[] = $temp;
                    } else {
                        Log::debug('Faculty/Staff Import: New employee detected from Excel', [
                            'row_number' => $i + 1,
                            'employee_id' => $temp['employee_id'],
                            'employee_name' => $temp['first_name'] . ' ' . $temp['last_name'],
                            'user_id' => Auth::id(),
                        ]);
                        $newData[] = $temp;
                    }
                }

                $request->session()->put('new_employee_data', $newData);
                $request->session()->put('existing_employee_data', $existingData);

                Log::info('Faculty/Staff Import: File processed and data stored in session', [
                    'new_employees_count' => count($newData),
                    'existing_employees_count' => count($existingData),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);
            } else {
                Log::debug('Faculty/Staff Import: Loading data from session for display', [
                    'user_id' => Auth::id(),
                ]);

                // GET request for pagination
                $newData = $newSessionData;
                $existingData = $existingSessionData;
            }

            $showTable = true;
            $new = !empty($newData);
            $existing = !empty($existingData);
            $perPage = $request->input('perPage', 10);

            Log::debug('Faculty/Staff Import: Preparing pagination', [
                'per_page' => $perPage,
                'new_data_count' => count($newData),
                'existing_data_count' => count($existingData),
                'user_id' => Auth::id(),
            ]);

            // Paginate New Data
            $newCurrentPage = LengthAwarePaginator::resolveCurrentPage('new');
            $newCurrentItems = array_slice($newData, ($newCurrentPage - 1) * $perPage, $perPage);
            $newPaginatedData = new LengthAwarePaginator($newCurrentItems, count($newData), $perPage, $newCurrentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'new',
            ]);

            // Paginate Existing Data
            $existingCurrentPage = LengthAwarePaginator::resolveCurrentPage('existing');
            $existingCurrentItems = array_slice($existingData, ($existingCurrentPage - 1) * $perPage, $perPage);
            $existingPaginatedData = new LengthAwarePaginator($existingCurrentItems, count($existingData), $perPage, $existingCurrentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'existing',
            ]);

        } catch (\Exception $e) {
            $errors = "An error occurred while loading the employees: " . $e->getMessage();

            Log::error('Faculty/Staff Import: Upload process failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
        }

        Log::info('Faculty/Staff Import: Upload view rendered successfully', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return view('import.employees.index', compact('showTable', 'newPaginatedData', 'existingPaginatedData', 'new', 'existing', 'perPage'));
    }

    /**
     * Downloads the template for faculties and staffs import in Excel format.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        Log::info('Faculty/Staff Import: Template download initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $filePath = public_path('excel/Employee-template.xlsx');

        if (File::exists($filePath)) {
            Log::info('Faculty/Staff Import: Template downloaded successfully', [
                'file_path' => $filePath,
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return Response::download($filePath, 'Employee-template.xlsx');
        }

        Log::error('Faculty/Staff Import: Template file not found', [
            'file_path' => $filePath,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        abort(404, 'File not found.');
    }

    /**
     * Extracts the first name, middle name, last name, and suffix from a full name.
     *
     * The full name is split into "Last" and "rest" (limit=2 in case there's a comma in names).
     * The "rest" is split into individual words.
     * If a suffix is found in the words (using the defined list of suffixes), the first name is the words before the suffix, the middle name is the words after the suffix, and the suffix is the matched suffix.
     * If no suffix is found, the first name is the first word, and the middle name is the rest of the words.
     *
     * @param string $fullName The full name of the employee.
     * @return array An associative array containing the first name, middle name, last name, and suffix of the employee.
     */
    private function extractNameParts(string $fullName): array
    {
        // Expand this list if you have more suffixes
        $suffixes = ['Jr', 'Jr.', 'Sr', 'Sr.', 'II', 'III', 'IV', 'V', 'PhD', 'MD', 'Esq'];

        // normalized suffix set for matching (strip trailing dot and lowercase)
        $normSuffixes = array_map(fn($s) => strtolower(rtrim($s, '.')), $suffixes);

        // split into "Last" and "rest" (limit=2 in case there's a comma in names)
        $parts = explode(',', $fullName, 2);
        $lastName = trim($parts[0] ?? '');
        $otherParts = trim($parts[1] ?? '');

        if ($otherParts === '') {
            return [
                'first_name'  => '',
                'middle_name' => '',
                'last_name'   => $lastName,
                'suffix'      => ''
            ];
        }

        $namePieces = preg_split('/\s+/', $otherParts);

        $firstName = '';
        $middleName = '';
        $suffix = '';

        // look for a suffix starting from the second word (suffix sits between first name and middle name)
        $suffixIndex = null;
        for ($i = 1; $i < count($namePieces); $i++) {
            $normalized = strtolower(rtrim($namePieces[$i], '.'));
            if (in_array($normalized, $normSuffixes, true)) {
                $suffixIndex = $i;
                $suffix = $namePieces[$i]; // keep original (with period if present)
                break;
            }
        }

        if ($suffixIndex !== null) {
            // first_name = all words before the suffix (so second given name is included)
            $firstName = implode(' ', array_slice($namePieces, 0, $suffixIndex));
            // middle_name = everything after the suffix
            $middleName = implode(' ', array_slice($namePieces, $suffixIndex + 1));
        } else {
            // no suffix found -> first is the first word, rest is middle name
            $firstName = $namePieces[0] ?? '';
            $middleName = count($namePieces) > 1 ? implode(' ', array_slice($namePieces, 1)) : '';
        }

        return [
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'suffix'      => $suffix,
        ];
    }

    /**
     * Sends an account notification email to the given user with the given password.
     *
     * Logs information about sending the account notification email.
     * If the email is sent successfully, logs the success.
     * If the email fails to send, logs the error with the error message.
     *
     * @param \App\Models\User $user The user to send the account notification email to.
     * @param string $password The password to include in the account notification email.
     */
    private function account_notification($user, $password)
    {
        Log::info('Faculty/Staff Import: Sending account notification email', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'sent_by' => Auth::id(),
            'timestamp' => now(),
        ]);

        try {
            Mail::to($user->email)->send(new AccountEmailMessage($user, $password));

            Log::info('Faculty/Staff Import: Account notification email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Faculty/Staff Import: Failed to send account notification email', [
                'user_id' => $user->id,
                'user_email' => $user->email,
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
     * @return array An array of enum values. If no enum values are found, returns ['N/A'].
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
