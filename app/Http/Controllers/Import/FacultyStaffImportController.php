<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\StagingUser;

class FacultyStaffImportController extends Controller
{
    public function index()
    {
        $showTable = false;
        return view('import.employees.index', compact('showTable'));
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
                    'gender'        => $item['gender'],
                    'email'         => $item['email'],
                    'password'      => Hash::make($item['password']),
                    'employee_id'   => $item['employee_id'],
                    'employee_role' => $item['employee_role'],
                    'user_type'     => 'employee',
                ]);
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
            DB::commit();
        }
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        return redirect()->route('import.import-faculties-staffs')->with('toast-success', 'Faculties & Staffs imported successfully');
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
            for($i = 1; $i < count($rows); $i++){
                $data[] = array(
                    'rfid'          => $rows[$i][0],
                    'first_name'    => $rows[$i][1],
                    'middle_name'   => $rows[$i][2],
                    'last_name'     => $rows[$i][3],
                    'suffix'        => $rows[$i][4],
                    'gender'        => $rows[$i][5],
                    'email'         => $rows[$i][6],
                    'password'      => $rows[$i][7],
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
}
