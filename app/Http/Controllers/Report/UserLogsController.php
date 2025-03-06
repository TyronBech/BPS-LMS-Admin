<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use DateTime;
use Carbon\Carbon;

class UserLogsController extends Controller
{
    public function index()
    {
        $inputName      = "";
        $inputLastName  = "";
        $fromInputDate  = "";
        $toInputDate    = "";
        $peak_hour      = "00:00";
        $data           = Log::with('users')->get();
        return view('report.users.user-logs', compact('data', 'inputName', 'inputLastName', 'fromInputDate', 'toInputDate', 'peak_hour'));
    }
    public function search(Request $request)
    {
        $inputName      = "";
        $inputLastName  = "";
        $fromInputDate  = "";
        $toInputDate    = "";
        $shownData      = null;
        $peak_hour      = "00:00";
        $validator = Validator::make($request->all(), [
            'start'         => 'sometimes',
            'end'           => 'sometimes',
            'last-name'     => 'sometimes',
            'first-name'    => 'sometimes',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if($request->input('shownData')) $shownData = json_decode($request->input('shownData'), true);
        if($request->input('submit') == 'pdf'){
            $this->generatePDF($shownData);
        }
        $data = $this->generateData($request);
        $hours = $data->map(function ($item) {
            $item = Carbon::parse($item->timestamp)->format('H:i:s'); // Or 'h:i:s A' for 12-hour format
            return $item;
        });
        $peak_hour = $this->findPeakHour($hours) . ":00";
        return view('report.users.user-logs', compact('data', 'inputName', 'inputLastName', 'fromInputDate', 'toInputDate', 'peak_hour'));
    }
    private function findPeakHour($times)
    {
        $hourCounts = array();
        foreach ($times as $time) {
            $hour = substr($time, 0, 2);
            $hourCounts[$hour] = isset($hourCounts[$hour]) ? $hourCounts[$hour] + 1 : 1;
        }
        if(count($hourCounts) == 0) return "00";
        $maxCount = 0;
        foreach ($hourCounts as $hour => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $peakHour = $hour;
            }
        }
        return $peakHour;
    }
    private function generatePDF($data)
    {
        // $collection = collect($data);
        // $arrayPdf   = array( 'data' => $collection );
        // $pdf        = Pdf::loadView('pdf.user-pdf-report-format', $arrayPdf);
        // $directory  = 'C:/Users/tyron/Downloads';
        // $pdf->save($directory . '/users-report_' . date('Y-m-d') . '.pdf');
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet(); 
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Log ID');
        $sheet->setCellValue('B1', 'RFID');
        $sheet->setCellValue('C1', 'Name');
        $sheet->setCellValue('D1', 'Middle Name');
        $sheet->setCellValue('E1', 'Surname');
        $sheet->setCellValue('F1', 'Date');
        $sheet->setCellValue('G1', 'Time');
        $sheet->setCellValue('H1', 'Action');
        $row = 2;
        foreach($data as $item){
            $sheet->setCellValue('A' . $row, $item->log_id);
            $sheet->setCellValue('B' . $row, $item->rfid_tag);
            $sheet->setCellValue('C' . $row, $item->first_name);
            $sheet->setCellValue('D' . $row, $item->middle_name);
            $sheet->setCellValue('E' . $row, $item->last_name);
            $sheet->setCellValue('F' . $row, $item->log_date);
            $sheet->setCellValue('G' . $row, $item->log_time);
            $sheet->setCellValue('H' . $row, $item->actiontype);
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $directory  = 'C:/Users/tyron/Downloads';
        $filename   = $directory . '/student-report_' . date('Y-m-d') . '.xlsx';
        $writer->save($filename);
    }
    private function generateData(Request $request)
    {
        $fromInputDate = $request->input('start');
        $toInputDate = $request->input('end');
        $inputName = strtolower($request->input('first-name'));
        $inputLastName = strtolower($request->input('last-name'));

        $query = Log::with('users');

        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(user_logs.timestamp)'), [$fromInputDate, $toInputDate]); // Corrected table name
        }

        if (strlen($inputName) > 0) {
            $query->whereHas('users', function ($q) use ($inputName) {
                $q->where(DB::raw('lower(first_name)'), 'like', '%' . $inputName . '%');
            });
        }

        if (strlen($inputLastName) > 0) {
            $query->whereHas('users', function ($q) use ($inputLastName) {
                $q->where(DB::raw('lower(last_name)'), 'like', '%' . $inputLastName . '%');
            });
        }

        $data = $query->orderBy(DB::raw('DATE(user_logs.timestamp)'), 'asc') // Corrected table name
            ->get();
        return $data;
    }
}
