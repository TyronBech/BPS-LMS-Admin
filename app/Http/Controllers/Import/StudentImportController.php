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

class StudentImportController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->has('page') && !$request->has('perPage')) {
            $request->session()->forget('new_student_data');
            $request->session()->forget('existing_student_data');
        }
        $showTable = false;
        return view("import.students.students", compact('showTable'));
    }
    public function store(Request $request)
    {
        $newStudentsFromSession = $request->session()->get('new_student_data', []);
        $existingStudentsFromSession = $request->session()->get('existing_student_data', []);

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

        $students               = array_merge($newStudentsFromSession ?? array(), $existingStudentsFromSession ?? array());
        $errors                 = null;
        $staged_users           = array();
        $newStudentsCount       = 0;
        $existingStudentsCount  = 0;
        $users                  = new User();
        // Merge both datasets so we can loop once
        DB::beginTransaction();
        foreach ($students as $item) {
            // skip empty rows
            if (empty(array_filter($item))) {
                continue;
            }
            $validator = Validator::make($item, [
                'rfid'          => 'nullable|string|min:10|regex:/^[0-9]+$/u',
                'first_name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'middle_name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'last_name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
                'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
                'id_number'     => 'required|string|min:10|regex:/^[0-9]+$/u',
                'grade_level'   => 'required|numeric|min:7|max:12',
                'section'       => 'required|string|max:50',
                'gender'        => 'required|string|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
                'email'         => 'required|string|email',
            ]);
            if ($validator->fails()) {
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for student: ' . $item['first_name'] . ' ' . $item['last_name'];
                return redirect()->route('import.import-students')->with('toast-error', $errors);
            }
            try {
                $existingStudent = User::whereHas('students', function ($query) use ($item) {
                    $query->where('id_number', $item['id_number']);
                })->with('students')->first();
                if ($existingStudent) {
                    if (
                        $existingStudent->rfid                  == $item['rfid']
                        && $existingStudent->first_name         == $item['first_name']
                        && $existingStudent->middle_name        == $item['middle_name']
                        && $existingStudent->last_name          == $item['last_name']
                        && $existingStudent->suffix             == $item['suffix']
                        && $existingStudent->gender             == $item['gender']
                        && $existingStudent->email              == $item['email']
                        && $existingStudent->students->level    == $item['grade_level']
                        && $existingStudent->students->section  == $item['section']
                    ) {
                        continue;
                    }
                    $existingStudent->update([
                        'rfid'          => $item['rfid'],
                        'first_name'    => $item['first_name'],
                        'middle_name'   => $item['middle_name'],
                        'last_name'     => $item['last_name'],
                        'suffix'        => $item['suffix'],
                        'gender'        => $item['gender'],
                        'email'         => $item['email'],
                    ]);
                    $existingStudent->students()->update([
                        'level'         => $item['grade_level'],
                        'section'       => $item['section'],
                    ]);
                    $existingStudentsCount++;
                } else {
                    if (User::where('email', $item['email'])->exists()) {
                        DB::rollBack();
                        $errors = "Email already exists for student: " . $item['first_name'] . " " . $item['last_name'];
                        return redirect()->route('import.import-students')->with('toast-error', $errors);
                    } else if (User::where('rfid', $item['rfid'])->exists()) {
                        DB::rollBack();
                        $errors = "RFID already exists for student: " . $item['first_name'] . " " . $item['last_name'];
                        return redirect()->route('import.import-students')->with('toast-error', $errors);
                    }
                    $password = Str::password(8, true, true, true, false);
                    StagingUser::create([
                        'rfid'          => $item['rfid'],
                        'first_name'    => $item['first_name'],
                        'middle_name'   => $item['middle_name'],
                        'last_name'     => $item['last_name'],
                        'suffix'        => $item['suffix'],
                        'gender'        => $item['gender'],
                        'email'         => $item['email'],
                        'password'      => Hash::make($password),
                        'id_number'     => $item['id_number'],
                        'level'         => $item['grade_level'],
                        'section'       => $item['section'],
                        'user_type'     => 'student',
                    ]);
                    $staged_users[] = [
                        'email' => $item['email'],
                        'password' => $password,
                    ];
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
                return redirect()->route('import.import-students')->with('toast-error', $errors);
            }
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        foreach ($staged_users as $user) {
            $student = User::where('email', $user['email'])->first();
            if (!$student) continue;
            $this->account_notification($student, $user['password']);
        }
        $request->session()->forget('new_student_data');
        $request->session()->forget('existing_student_data');
        return redirect()->route('import.import-students')->with('toast-success', 'Students imported successfully: ' . $newStudentsCount . ' new students added & ' . $existingStudentsCount . ' existing students updated.');
    }
    public function upload(Request $request)
    {
        try {
            $newSessionData = $request->session()->get('new_student_data', []);
            $existingSessionData = $request->session()->get('existing_student_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
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
                // Initial file upload
                $file = $request->file('file');
                $reader = new ReaderXlsx();
                $spreadsheet = $reader->load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                $newData = [];
                $existingData = [];

                if ($rows[0][0] == null) {
                    return redirect()->route('import.import-students')->with('toast-error', "Excel file is empty.");
                }

                for ($i = 19; $i < count($rows); $i++) {
                    if (empty(array_filter(array_slice($rows[$i], 1, 7)))) continue;

                    $fullName = $this->extractNameParts($rows[$i][2] ?? '');
                    if (empty($fullName['first_name']) || empty($fullName['last_name'])) {
                        return redirect()->route('import.import-students')->with('toast-error', "Invalid format in row " . ($i + 1) . ". Please ensure that the 'Full Name' field are correctly filled.");
                    }
                    $temp = [
                        'rfid' => $rows[$i][1],
                        'first_name' => $fullName['first_name'],
                        'middle_name' => $fullName['middle_name'],
                        'last_name' => $fullName['last_name'],
                        'suffix' => $fullName['suffix'],
                        'gender' => $rows[$i][3],
                        'email' => $rows[$i][4],
                        'id_number' => $rows[$i][5],
                        'grade_level' => $rows[$i][6],
                        'section' => $rows[$i][7],
                    ];
                    if (StudentDetail::where('id_number', $temp['id_number'])->exists()) {
                        $existingData[] = $temp;
                    } else {
                        $newData[] = $temp;
                    }
                }
                $request->session()->put('new_student_data', $newData);
                $request->session()->put('existing_student_data', $existingData);
            } else {
                // GET request for pagination
                $newData = $newSessionData;
                $existingData = $existingSessionData;
            }

            $showTable = true;
            $new = !empty($newData);
            $existing = !empty($existingData);
            $perPage = $request->input('perPage', 10);

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
            return redirect()->route('import.import-students')->with('toast-error', $errors);
        }
        return view('import.students.students', compact('showTable', 'newPaginatedData', 'existingPaginatedData', 'new', 'existing', 'perPage'));
    }
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Student-template.xlsx');

        if (File::exists($filePath)) {
            return Response::download($filePath, 'Student-template.xlsx');
        }
        abort(404, 'File not found.');
    }
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
}
