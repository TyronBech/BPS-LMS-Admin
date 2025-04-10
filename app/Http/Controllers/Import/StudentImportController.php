<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use App\Models\StagingUser;

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
        foreach ($dataArray as $item) {
            DB::beginTransaction();
            try {
                StagingUser::create([
                    'rfid'          => $item['rfid'],
                    'first_name'    => $item['first_name'],
                    'middle_name'   => $item['middle_name'],
                    'last_name'     => $item['last_name'],
                    'suffix'        => $item['suffix'],
                    'email'         => $item['email'],
                    'password'      => Hash::make($item['password']),
                    'lrn'           => $item['lrn'],
                    'grade_level'   => $item['grade_level'],
                    'section'       => $item['section'],
                    'user_type'     => $item['user_type'],
                    'group_name'    => $item['group_name'],
                ]);
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
            DB::commit();
        }
        DB::beginTransaction();
        try{
            DB::statement('SET SQL_SAFE_UPDATES = 0');
            DB::statement('CALL DistributeStagingUsers()');
            DB::statement('SET SQL_SAFE_UPDATES = 1');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        DB::commit();
        return redirect()->route('import.import-students')->with('toast-success', 'Students imported successfully');
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
            } else if(count($rows[0]) > 12 || count($rows[0]) < 12){
                return redirect()->route('import.import-students')->with('toast-error', "An error occurred while saving student: Wrong number of columns.");
            }
            for($i = 1; $i < count($rows); $i++){
                $data[] = array(
                    'rfid'          => $rows[$i][0],
                    'first_name'    => $rows[$i][1],
                    'middle_name'   => $rows[$i][2],
                    'last_name'     => $rows[$i][3],
                    'suffix'        => $rows[$i][4],
                    'email'         => $rows[$i][5],
                    'password'      => $rows[$i][6],
                    'lrn'           => $rows[$i][7],
                    'grade_level'   => $rows[$i][8],
                    'section'       => $rows[$i][9],
                    'user_type'     => $rows[$i][10],
                    'group_name'    => $rows[$i][11],    
                );
            }
        } catch(\Exception $e){
            $errors = "An error occurred while loading the students";
            return redirect()->route('import.import-students')->with('toast-error', $errors);
        }
        return view('import.students.students', compact('showTable', 'data'));
    }
}
