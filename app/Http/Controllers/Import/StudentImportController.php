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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class StudentImportController extends Controller
{
    public function index()
    {
        $showTable = false;
        return view("import.students.students", compact('showTable'));
    }
    public function store(Request $request)
    {
        $data       = $request->input('data');
        $dataArray  = json_decode($data, true);
        $errors     = "";
        $staged_users           = array();
        $newStudentsCount       = 0;
        $existingStudentsCount  = 0;
        DB::beginTransaction();
        foreach ($dataArray as $item) {
            try {
                $existingStudent = User::whereHas('students', function ($query) use ($item) {
                    $query->where('id_number', $item['id_number']);
                })->with('students')->first();
                if($existingStudent){
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
                    if(User::where('email', $item['email'])->exists()){
                        $errors = "Email already exists for student: " . $item['first_name'] . " " . $item['last_name'];
                        return redirect()->route('import.import-students')->with('toast-error', $errors);
                    } else if(User::where('rfid', $item['rfid'])->exists()){
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
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        foreach ($staged_users as $user) {
            $student = User::where('email', $user['email'])->first();
            if(!$student) continue;
            $this->account_notification($student, $user['password']);
        }
        return redirect()->route('import.import-students')->with('toast-success', 'Students imported successfully: ' . $newStudentsCount . ' new students added & ' . $existingStudentsCount . ' existing students updated.');
    }
    public function upload(Request $request)
    {
        try{
            if($request->file('file') == null) return redirect()->route('import.import-students')->with('toast-warning', "Please select a file.");
            $showTable      = true;
            $file           = $request->file('file');
            $reader         = new ReaderXlsx();
            $spreadsheet    = $reader->load($file);
            $sheet          = $spreadsheet->getActiveSheet();
            $rows           = $sheet->toArray();
            $data           = array();
            if($rows[0][0] == null){
                return redirect()->route('import.import-students')->with('toast-error', "Excel file is empty.");
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
                    'id_number'     => $rows[$i][8],
                    'grade_level'   => $rows[$i][9],
                    'section'       => $rows[$i][10],   
                );
            }
        } catch(\Exception $e){
            $errors = "An error occurred while loading the students";
            return redirect()->route('import.import-students')->with('toast-error', $errors);
        }
        return view('import.students.students', compact('showTable', 'data'));
    }
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Student-template.xlsx');

        if (File::exists($filePath)) {
            return Response::download($filePath, 'Student-template.xlsx');
        }
        abort(404, 'File not found.');
    }
    private function account_notification($user, $password){
        Mail::to($user->email)->send(new AccountEmailMessage($user, $password));
    }
}
