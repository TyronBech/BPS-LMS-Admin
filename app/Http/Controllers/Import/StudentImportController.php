<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use App\Models\StagingUser;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountEmailMessage;
use App\Models\StudentDetail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentImportController extends Controller
{
    /**
     * Index page of Student Import
     *
     * This function handles the index page of the Student Import feature.
     * It logs the user's access and clears the session data if the user did not come from a pagination link.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::info('Student Import: Index page accessed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            Log::debug('Student Import: Clearing session data', [
                'user_id' => Auth::id(),
                'action' => 'session_clear',
                'cleared_keys' => ['new_student_data', 'existing_student_data'],
            ]);

            $request->session()->forget('new_student_data');
            $request->session()->forget('existing_student_data');
        }

        $showTable = false;
        return view("import.students.students", compact('showTable'));
    }

    /**
     * Stores the imported students in the database
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function store(Request $request)
    {
        Log::info('Student Import: Store process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $newStudentsFromSession = $request->session()->get('new_student_data', []);
        $existingStudentsFromSession = $request->session()->get('existing_student_data', []);

        Log::debug('Student Import: Retrieved session data', [
            'new_students_count' => count($newStudentsFromSession),
            'existing_students_count' => count($existingStudentsFromSession),
            'user_id' => Auth::id(),
        ]);

        // Merge last submitted data
        $submittedNew = $request->input('new_students', []);
        foreach ($submittedNew as $index => $student) {
            if (isset($newStudentsFromSession[$index])) {
                $newStudentsFromSession[$index] = array_merge($newStudentsFromSession[$index], $student);
            }
        }

        $submittedExisting = $request->input('existing_students', []);
        foreach ($submittedExisting as $index => $student) {
            if (isset($existingStudentsFromSession[$index])) {
                $existingStudentsFromSession[$index] = array_merge($existingStudentsFromSession[$index], $student);
            }
        }

        $students = array_merge($newStudentsFromSession ?? array(), $existingStudentsFromSession ?? array());
        Log::info('Student Import: Total students to process', [
            'total_count' => count($students),
            'user_id' => Auth::id(),
        ]);

        $errors = null;
        $staged_users = array();
        $newStudentsCount = 0;
        $existingStudentsCount = 0;
        $users = new User();

        DB::beginTransaction();
        Log::info('Student Import: Database transaction started', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        foreach ($students as $index => $item) {
            // skip empty rows
            if (empty(array_filter($item))) {
                Log::debug('Student Import: Skipping empty row', [
                    'row_index' => $index,
                    'user_id' => Auth::id(),
                ]);
                continue;
            }

            Log::debug('Student Import: Validating student data', [
                'row_index' => $index,
                'student_name' => ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''),
                'id_number' => $item['id_number'] ?? 'N/A',
                'email' => $item['email'] ?? 'N/A',
                'user_id' => Auth::id(),
            ]);

            $validator = Validator::make($item, [
                'rfid' => 'nullable|string|min:10|regex:/^[0-9]+$/u',
                'first_name' => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'middle_name' => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'last_name' => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'suffix' => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
                'id_number' => 'required|string|min:10|regex:/^[0-9]+$/u',
                'grade_level' => 'required|numeric|min:7|max:12',
                'section' => 'required|string|max:50',
                'gender' => 'required|string|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
                'email' => 'required|string|email',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for student: ' . $item['first_name'] . ' ' . $item['last_name'];

                Log::error('Student Import: Validation failed', [
                    'row_index' => $index,
                    'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                    'error_message' => $validator->errors()->first(),
                    'failed_fields' => $validator->errors()->keys(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-students')->with('toast-error', $errors);
            }

            try {
                $existingStudent = User::whereHas('students', function ($query) use ($item) {
                    $query->where('id_number', $item['id_number']);
                })->with('students')->first();

                if ($existingStudent) {
                    Log::info('Student Import: Existing student found', [
                        'student_id' => $existingStudent->id,
                        'student_name' => $existingStudent->full_name,
                        'id_number' => $item['id_number'],
                        'user_id' => Auth::id(),
                    ]);

                    if (
                        $existingStudent->rfid == $item['rfid']
                        && $existingStudent->first_name == $item['first_name']
                        && $existingStudent->middle_name == $item['middle_name']
                        && $existingStudent->last_name == $item['last_name']
                        && $existingStudent->suffix == $item['suffix']
                        && $existingStudent->gender == $item['gender']
                        && $existingStudent->email == $item['email']
                        && $existingStudent->students->level == $item['grade_level']
                        && $existingStudent->students->section == $item['section']
                    ) {
                        Log::debug('Student Import: No changes detected, skipping update', [
                            'student_id' => $existingStudent->id,
                            'id_number' => $item['id_number'],
                            'user_id' => Auth::id(),
                        ]);
                        continue;
                    }

                    $oldData = [
                        'rfid' => $existingStudent->rfid,
                        'first_name' => $existingStudent->first_name,
                        'middle_name' => $existingStudent->middle_name,
                        'last_name' => $existingStudent->last_name,
                        'suffix' => $existingStudent->suffix,
                        'gender' => $existingStudent->gender,
                        'email' => $existingStudent->email,
                        'level' => $existingStudent->students->level,
                        'section' => $existingStudent->students->section,
                    ];

                    $existingStudent->update([
                        'rfid' => $item['rfid'],
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name'],
                        'last_name' => $item['last_name'],
                        'suffix' => $item['suffix'],
                        'gender' => $item['gender'],
                        'email' => $item['email'],
                    ]);

                    $existingStudent->students()->update([
                        'level' => $item['grade_level'],
                        'section' => $item['section'],
                    ]);

                    Log::info('Student Import: Student updated successfully', [
                        'student_id' => $existingStudent->id,
                        'id_number' => $item['id_number'],
                        'old_data' => $oldData,
                        'new_data' => $item,
                        'modified_by' => Auth::id(),
                        'modified_by_name' => Auth::user()->full_name,
                        'timestamp' => now(),
                    ]);

                    $existingStudentsCount++;
                } else {
                    Log::debug('Student Import: Checking for duplicate email/RFID', [
                        'email' => $item['email'],
                        'rfid' => $item['rfid'],
                        'user_id' => Auth::id(),
                    ]);

                    if (User::where('email', $item['email'])->exists()) {
                        DB::rollBack();
                        $errors = "Email already exists for student: " . $item['first_name'] . " " . $item['last_name'];

                        Log::error('Student Import: Duplicate email detected', [
                            'email' => $item['email'],
                            'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-students')->with('toast-error', $errors);
                    } else if (User::where('rfid', $item['rfid'])->exists()) {
                        DB::rollBack();
                        $errors = "RFID already exists for student: " . $item['first_name'] . " " . $item['last_name'];

                        Log::error('Student Import: Duplicate RFID detected', [
                            'rfid' => $item['rfid'],
                            'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-students')->with('toast-error', $errors);
                    }

                    $password = Str::password(8, true, true, true, false);

                    Log::info('Student Import: Creating new student in staging', [
                        'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                        'email' => $item['email'],
                        'id_number' => $item['id_number'],
                        'grade_level' => $item['grade_level'],
                        'section' => $item['section'],
                        'created_by' => Auth::id(),
                        'created_by_name' => Auth::user()->full_name,
                        'timestamp' => now(),
                    ]);

                    StagingUser::create([
                        'rfid' => $item['rfid'],
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name'],
                        'last_name' => $item['last_name'],
                        'suffix' => $item['suffix'],
                        'gender' => $item['gender'],
                        'email' => $item['email'],
                        'password' => Hash::make($password),
                        'id_number' => $item['id_number'],
                        'level' => $item['grade_level'],
                        'section' => $item['section'],
                        'user_type' => 'student',
                    ]);

                    $staged_users[] = [
                        'email' => $item['email'],
                        'password' => $password,
                    ];

                    Log::debug('Student Import: Student staged successfully', [
                        'email' => $item['email'],
                        'user_id' => Auth::id(),
                    ]);

                    $newStudentsCount++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if ($e->getCode() == 23000) {
                    $errors = "Duplicate ID found for student: " . $item['first_name'] . " " . $item['last_name'];
                } else if ($e->getCode() == "HY000") {
                    $errors = "An error occurred while saving student: Wrong format of excel file";
                } else {
                    $errors = "An error occurred while saving student: " . $e->getMessage();
                }

                Log::error('Student Import: Database error occurred', [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                    'sql_state' => $e->errorInfo[0] ?? 'N/A',
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-students')->with('toast-error', $errors);
            }
        }

        DB::commit();
        Log::info('Student Import: Database transaction committed', [
            'new_students' => $newStudentsCount,
            'updated_students' => $existingStudentsCount,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        try {
            Log::info('Student Import: Executing stored procedure to distribute staged users', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            DB::statement('CALL DistributeStagingUsers()');

            Log::info('Student Import: Stored procedure executed successfully', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Student Import: Stored procedure execution failed', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }

        Log::info('Student Import: Sending account notification emails', [
            'email_count' => count($staged_users),
            'user_id' => Auth::id(),
        ]);

        foreach ($staged_users as $user) {
            $student = User::where('email', $user['email'])->first();
            if (!$student) {
                Log::warning('Student Import: Student not found for email notification', [
                    'email' => $user['email'],
                    'user_id' => Auth::id(),
                ]);
                continue;
            }

            Log::debug('Student Import: Sending email to student', [
                'student_id' => $student->id,
                'student_email' => $user['email'],
                'user_id' => Auth::id(),
            ]);

            $this->account_notification($student, $user['password']);

            Log::info('Student Import: Email sent successfully', [
                'student_id' => $student->id,
                'student_email' => $user['email'],
                'sent_by' => Auth::id(),
                'timestamp' => now(),
            ]);
        }

        $request->session()->forget('new_student_data');
        $request->session()->forget('existing_student_data');

        Log::info('Student Import: Process completed successfully', [
            'new_students_count' => $newStudentsCount,
            'updated_students_count' => $existingStudentsCount,
            'emails_sent' => count($staged_users),
            'completed_by' => Auth::id(),
            'completed_by_name' => Auth::user()->full_name,
            'timestamp' => now(),
        ]);

        return redirect()->route('import.import-students')->with('toast-success', 'Students imported successfully: ' . $newStudentsCount . ' new students added & ' . $existingStudentsCount . ' existing students updated.');
    }

    /**
     * Handles the upload of a students Excel file.
     *
     * This function logs the user's access, validates the file,
     * processes the Excel file, and stores the extracted data in the session.
     * It also handles pagination and displays the uploaded data in a table.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        Log::info('Student Import: Upload process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'has_file' => $request->hasFile('file'),
            'timestamp' => now(),
        ]);

        try {
            $newSessionData = $request->session()->get('new_student_data', []);
            $existingSessionData = $request->session()->get('existing_student_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
                Log::debug('Student Import: Processing pagination request', [
                    'user_id' => Auth::id(),
                    'new_students_in_session' => count($newSessionData),
                    'existing_students_in_session' => count($existingSessionData),
                ]);

                // POST request for pagination, merge edits
                $submittedNew = $request->input('new_students', []);
                foreach ($submittedNew as $index => $student) {
                    if (isset($newSessionData[$index])) {
                        $newSessionData[$index] = array_merge($newSessionData[$index], $student);
                    }
                }
                $request->session()->put('new_student_data', $newSessionData);

                $submittedExisting = $request->input('existing_students', []);
                foreach ($submittedExisting as $index => $student) {
                    if (isset($existingSessionData[$index])) {
                        $existingSessionData[$index] = array_merge($existingSessionData[$index], $student);
                    }
                }
                $request->session()->put('existing_student_data', $existingSessionData);

                $newData = $newSessionData;
                $existingData = $existingSessionData;

            } else if ($request->hasFile('file')) {
                $file = $request->file('file');

                Log::info('Student Import: Processing uploaded file', [
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

                Log::debug('Student Import: Excel file loaded', [
                    'total_rows' => count($rows),
                    'user_id' => Auth::id(),
                ]);

                $newData = [];
                $existingData = [];

                if ($rows[0][0] == null) {
                    Log::error('Student Import: Empty Excel file', [
                        'file_name' => $file->getClientOriginalName(),
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-students')->with('toast-error', "Excel file is empty.");
                }

                for ($i = 19; $i < count($rows); $i++) {
                    if (empty(array_filter(array_slice($rows[$i], 1, 7)))) {
                        Log::debug('Student Import: Skipping empty row in Excel', [
                            'row_number' => $i + 1,
                            'user_id' => Auth::id(),
                        ]);
                        continue;
                    }

                    $fullName = $this->extractNameParts($rows[$i][2] ?? '');
                    if (empty($fullName['first_name']) || empty($fullName['last_name'])) {
                        Log::error('Student Import: Invalid name format in Excel', [
                            'row_number' => $i + 1,
                            'full_name' => $rows[$i][2] ?? '',
                            'user_id' => Auth::id(),
                            'timestamp' => now(),
                        ]);

                        return redirect()->route('import.import-students')->with('toast-error', "Invalid format in row " . ($i + 1) . ". Please ensure that the 'Full Name' field are correctly filled.");
                    }

                    $temp = [
                        'rfid'          => $rows[$i][1],
                        'first_name'    => $fullName['first_name'],
                        'middle_name'   => $fullName['middle_name'],
                        'last_name'     => $fullName['last_name'],
                        'suffix'        => $rows[$i][3],
                        'gender'        => $rows[$i][4],
                        'email'         => $rows[$i][5],
                        'id_number'     => $rows[$i][6],
                        'grade_level'   => $rows[$i][7],
                        'section'       => $rows[$i][8],
                    ];

                    if (StudentDetail::where('id_number', $temp['id_number'])->exists()) {
                        Log::debug('Student Import: Existing student detected from Excel', [
                            'row_number' => $i + 1,
                            'id_number' => $temp['id_number'],
                            'student_name' => $temp['first_name'] . ' ' . $temp['last_name'],
                            'user_id' => Auth::id(),
                        ]);
                        $existingData[] = $temp;
                    } else {
                        Log::debug('Student Import: New student detected from Excel', [
                            'row_number' => $i + 1,
                            'id_number' => $temp['id_number'],
                            'student_name' => $temp['first_name'] . ' ' . $temp['last_name'],
                            'user_id' => Auth::id(),
                        ]);
                        $newData[] = $temp;
                    }
                }

                $request->session()->put('new_student_data', $newData);
                $request->session()->put('existing_student_data', $existingData);

                Log::info('Student Import: File processed and data stored in session', [
                    'new_students_count' => count($newData),
                    'existing_students_count' => count($existingData),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);
            } else {
                Log::debug('Student Import: Loading data from session for display', [
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

            Log::debug('Student Import: Preparing pagination', [
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
            $errors = "An error occurred while loading the students: " . $e->getMessage();

            Log::error('Student Import: Upload process failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->route('import.import-students')->with('toast-error', $errors);
        }

        Log::info('Student Import: Upload view rendered successfully', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return view('import.students.students', compact('showTable', 'newPaginatedData', 'existingPaginatedData', 'new', 'existing', 'perPage'));
    }

    /**
     * Downloads the template for students import in Excel format.
     *
     * Logs the user's access and successful download of the template.
     * If the template file is not found, logs an error and aborts with a 404 status code.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        Log::info('Student Import: Template download initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $filePath = public_path('excel/Student-template.xlsx');

        if (File::exists($filePath)) {
            Log::info('Student Import: Template downloaded successfully', [
                'file_path' => $filePath,
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return Response::download($filePath, 'Student-template.xlsx');
        }

        Log::error('Student Import: Template file not found', [
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
            Log::debug('Student Import: No other name parts found, only last name extracted', [
                'full_name' => $fullName,
                'last_name' => $lastName,
            ]);
            return [
                'first_name' => '',
                'middle_name' => '',
                'last_name' => $lastName,
                'suffix' => ''
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
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
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
        Log::info('Student Import: Sending account notification email', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'sent_by' => Auth::id(),
            'timestamp' => now(),
        ]);

        try {
            Mail::to($user->email)->send(new AccountEmailMessage($user, $password));

            Log::info('Student Import: Account notification email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Student Import: Failed to send account notification email', [
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
