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

class FacultyStaffImportController extends Controller
{
    public function index()
    {
        $showTable = false;
        return view('import.employees.index', compact('showTable'));
    }
    public function store(Request $request)
    {
        $newEmployees           = $request->input('new_employees');
        $existingEmployees      = $request->input('existing_employees');
        $dataArray              = array_merge($newEmployees ?? array(), $existingEmployees ?? array());
        $errors                 = null;
        $staged_users           = array();
        $newFacultiesCount      = 0;
        $existingFacultiesCount = 0;
        $users                  = new User();
        DB::beginTransaction();
        foreach ($dataArray as $item) {
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
                'gender'        => 'required|string|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
                'email'         => 'required|string|email|max:255',
                'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
                'employee_id'   => 'required|string|min:10|regex:/^[0-9]+$/u',
            ]);
            if ($validator->fails()) {
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for faculty/staff: ' . $item['first_name'] . ' ' . $item['last_name'];
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
            }
            try {
                $password = Str::password(8, true, true, true, false);
                $existingEmployee = User::wherehas('employees', function ($query) use ($item) {
                    $query->where('employee_id', $item['employee_id']);
                })->with('employees')->first();
                if ($existingEmployee) {
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
                        continue;
                    }
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
                    $existingFacultiesCount++;
                } else {
                    if (StagingUser::where('email', $item['email'])->exists()) {
                        DB::rollBack();
                        $errors = "Email already exists for user: " . $item['first_name'] . " " . $item['last_name'];
                        return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
                    } else if (StagingUser::where('rfid', $item['rfid'])->exists()) {
                        DB::rollBack();
                        $errors = "RFID already exists for user: " . $item['first_name'] . " " . $item['last_name'];
                        return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
                    }
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
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
            }
        }
        DB::commit();
        try {
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        foreach ($staged_users as $user) {
            $employee = User::where('email', $user['email'])->first();
            if ($employee == null) continue;
            $this->account_notification($employee, $user['password']);
        }
        return redirect()->route('import.import-faculties-staffs')->with('toast-success', 'Faculties & Staffs imported successfully: ' . $newFacultiesCount . ' added & ' . $existingFacultiesCount . ' updated');
    }
    public function upload(Request $request)
    {
        try {
            if ($request->file('file') == null) return redirect()->route('import.import-faculties-staffs')->with('toast-warning', "Please select a file.");
            $showTable      = true;
            $file           = $request->file('file');
            $reader         = new ReaderXlsx();
            $spreadsheet    = $reader->load($file);
            $sheet          = $spreadsheet->getActiveSheet();
            $rows           = $sheet->toArray();
            $newData        = array();
            $existingData   = array();
            $new            = false;
            $existing       = false;
            if ($rows[0][0] == null) {
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', "Excel file is empty.");
            } else if (count($rows[0]) > 11 || count($rows[0]) < 11) {
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', "An error occurred while saving faculties & staffs: Wrong number of columns.");
            }
            for ($i = 19; $i < count($rows); $i++) {
                if (
                    $rows[$i][1] == null &&
                    $rows[$i][2] == null &&
                    $rows[$i][3] == null &&
                    $rows[$i][4] == null &&
                    $rows[$i][5] == null &&
                    $rows[$i][6] == null &&
                    $rows[$i][7] == null
                ) continue;
                $fullName = $this->extractNameParts($rows[$i][2] ?? '');
                if (empty($fullName['first_name']) || empty($fullName['last_name']) || empty($fullName['last_name'])) {
                    return redirect()->route('import.import-faculties-staffs')->with('toast-error', "Invalid format in row " . ($i + 1) . ". Please ensure that the 'Full Name' field are correctly filled.");
                }
                $temp = array(
                    'rfid'          => $rows[$i][1],
                    'first_name'    => $fullName['first_name'],
                    'middle_name'   => $fullName['middle_name'],
                    'last_name'     => $fullName['last_name'],
                    'suffix'        => $rows[$i][3],
                    'gender'        => $rows[$i][4],
                    'email'         => $rows[$i][5],
                    'employee_id'   => $rows[$i][6],
                    'employee_role' => $rows[$i][7],
                );
                if (EmployeeDetail::where('employee_id', $temp['employee_id'])->exists()) {
                    $existingData[] = $temp;
                    $existing = true;
                } else {
                    $newData[] = $temp;
                    $new = true;
                }
            }
        } catch (\Exception $e) {
            $errors = "An error occurred while loading the students";
            return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
        }
        return view('import.employees.index', compact('showTable', 'newData', 'existingData', 'new', 'existing'));
    }
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Employee-template.xlsx');

        if (File::exists($filePath)) {
            return Response::download($filePath, 'Employee-template.xlsx');
        }
        abort(404, 'File not found.');
    }
    private function extractNameParts(String $fullName): array
    {
        $parts = explode(',', $fullName);
        $lastName = trim($parts[0] ?? '');
        // Handle "FirstName MiddleName" part
        $otherParts = trim($parts[1] ?? '');
        $namePieces = preg_split('/\s+/', $otherParts);
        $firstName = $namePieces[0] ?? '';
        $middleName = isset($namePieces[1]) ? implode(' ', array_slice($namePieces, 1)) : '';
        return [
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
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
