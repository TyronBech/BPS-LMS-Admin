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
        $data                   = $request->input('data');
        $dataArray              = json_decode($data, true);
        $errors                 = null;
        $staged_users           = array();
        $newFacultiesCount      = 0;
        $existingFacultiesCount = 0;
        $users                  = new User();
        DB::beginTransaction();
        foreach ($dataArray as $item) {
            $validator = Validator::make($item, [
                'rfid'          => 'required|string',
                'first_name'    => 'required|string|max:50',
                'middle_name'   => 'nullable|string|max:50',
                'last_name'     => 'required|string|max:50',
                'suffix'        => 'nullable|string|max:10',
                'gender'        => 'required|string|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
                'email'         => 'required|string|email|max:255',
                'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
                'employee_id'   => 'required|string|min:10',
            ]);
            if($validator->fails()){
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
                    if ($existingEmployee->rfid                     == $item['rfid']
                    && $existingEmployee->first_name                == $item['first_name']
                    && $existingEmployee->middle_name               == $item['middle_name']
                    && $existingEmployee->last_name                 == $item['last_name']
                    && $existingEmployee->suffix                    == $item['suffix']
                    && $existingEmployee->gender                    == $item['gender']
                    && $existingEmployee->email                     == $item['email']
                    && $existingEmployee->employees->employee_role  == $item['employee_role']
                    && $existingEmployee->employees->employee_id    == $item['employee_id']) 
                    { continue; }
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
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        foreach ($staged_users as $user) {
            $employee = User::where('email', $user['email'])->first();
            if($employee == null) continue;
            $this->account_notification($employee, $user['password']);
        }
        return redirect()->route('import.import-faculties-staffs')->with('toast-success', 'Faculties & Staffs imported successfully: ' . $newFacultiesCount . ' added & ' . $existingFacultiesCount . ' updated');
    }
    public function upload(Request $request)
    {
        try{
            if($request->file('file') == null) return redirect()->route('import.import-faculties-staffs')->with('toast-warning', "Please select a file.");
            $showTable      = true;
            $file           = $request->file('file');
            $reader         = new ReaderXlsx();
            $spreadsheet    = $reader->load($file);
            $sheet          = $spreadsheet->getActiveSheet();
            $rows           = $sheet->toArray();
            $data           = array();
            if($rows[0][0] == null){
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', "Excel file is empty.");
            } else if(count($rows[0]) > 11 || count($rows[0]) < 11){
                return redirect()->route('import.import-faculties-staffs')->with('toast-error', "An error occurred while saving faculties & staffs: Wrong number of columns.");
            }
            for($i = 19; $i < count($rows); $i++){
                $data[] = array(
                    'rfid'          => $rows[$i][1],
                    'first_name'    => $rows[$i][2],
                    'middle_name'   => $rows[$i][3],
                    'last_name'     => $rows[$i][4],
                    'suffix'        => $rows[$i][5],
                    'gender'        => $rows[$i][6],
                    'email'         => $rows[$i][7],
                    'employee_id'   => $rows[$i][8],
                    'employee_role' => $rows[$i][9], 
                );
            }
        } catch(\Exception $e){
            $errors = "An error occurred while loading the students";
            return redirect()->route('import.import-faculties-staffs')->with('toast-error', $errors);
        }
        return view('import.employees.index', compact('showTable', 'data'));
    }
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Employee-template.xlsx');

        if (File::exists($filePath)) {
            return Response::download($filePath, 'Employee-template.xlsx');
        }
        abort(404, 'File not found.');
    }
    private function account_notification($user, $password){
        Mail::to($user->email)->send(new AccountEmailMessage($user, $password));
    }
    private function extract_enums($table, $columnName){
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
