<?php

namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Log; // Import the Log model
use Illuminate\Support\Facades\DB; // Import the DB facade
use Carbon\Carbon; // Import the Carbon class
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use DateTime; // Import the DateTime class
use Barryvdh\DomPDF\Facade\Pdf; // Import the Pdf facade
use Dompdf\Dompdf; // Import the Dompdf class
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Import the Spreadsheet class
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx; // Import the WriterXlsx class with alias

class ComputerUseController extends Controller
{
    public function index()
    {
        $inputName      = null;
        $fromInputDate  = null;
        $toInputDate    = null;
        $peak_hour      = "00:00";
        $data = Log::with(['user.students']) // correct singular relationship
                ->where('computer_use', 'Yes')
                ->whereHas('user.students')
                ->orderBy(DB::raw('DATE(timestamp)'), 'desc')
                ->orderBy(DB::raw('TIME(timestamp)'), 'desc')
                ->get();
        $hours = $data->map(function ($item) {
            $item = Carbon::parse($item->timestamp)->format('H:i:s');
            return $item;
        });
        $hour = $this->findPeakHour($hours);
        if ($hour == 12) {
            $peak_hour = "12:00 PM";
        } else if ($hour == 0) {
            $peak_hour = "12:00 AM";
        } else if ($hour > 12) {
            $peak_hour = $hour - 12 . ":00 PM";
        } else {
            $peak_hour = $hour . ":00 AM";
        }
        return view('report.computers.index', compact('data', 'inputName', 'fromInputDate', 'toInputDate', 'peak_hour'));
    }
    public function search(Request $request)
    {
        $inputName      = $request->input('first-name');
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $peak_hour      = "00:00";
        $validator = Validator::make($request->all(), [
            'start'         => 'sometimes',
            'end'           => 'sometimes',
            'last-name'     => 'sometimes',
            'first-name'    => 'sometimes',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request);
            $this->generatePDF($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request);
            $this->exportExcel($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request);
        $hours = $data->map(function ($item) {
            $item = Carbon::parse($item->timestamp)->format('H:i:s');
            return $item;
        });
        $hour = $this->findPeakHour($hours);
        if ($hour == 12) {
            $peak_hour = "12:00 PM";
        } else if ($hour == 0) {
            $peak_hour = "12:00 AM";
        } else if ($hour > 12) {
            $peak_hour = $hour - 12 . ":00 PM";
        } else {
            $peak_hour = $hour . ":00 AM";
        }
        return view('report.computers.index', compact('data', 'inputName', 'fromInputDate', 'toInputDate', 'peak_hour'));
    }
    private function findPeakHour($times)
    {
        $hourCounts = array();
        foreach ($times as $time) {
            $hour = substr($time, 0, 2);
            $hourCounts[$hour] = isset($hourCounts[$hour]) ? $hourCounts[$hour] + 1 : 1;
        }
        if (count($hourCounts) == 0) return "00";
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
        $items = [
            'title' => 'Users Report',
            'date' => date('m/d/y'),
            'data' => $data,
            'totalCount' => $data->count(),
        ];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('pdf.computer-pdf-report', $items));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('computer-use-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Level');
        $sheet->setCellValue('C1', 'Section');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'Time');
        $row = 2;
        foreach ($data as $item) {
            if(!$item->user) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            $sheet->setCellValue('B' . $row, $item->user->students->level);
            $sheet->setCellValue('C' . $row, $item->user->students->section);
            $sheet->setCellValue('D' . $row, Carbon::parse($item->timestamp)->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, Carbon::parse($item->timestamp)->format('H:i:s'));
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'computer-use-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit();
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('first-name'));

        $query = Log::with(['user.students'])->whereHas('user.students');

        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(log_user_logs.timestamp)'), [$fromInputDate, $toInputDate]); // Corrected table name
        }

        if (strlen($inputName) > 0) {
            $query->with(['user.students'])->whereHas('user', function ($q) use ($inputName) {
                $q->where(DB::raw('lower(first_name)'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(last_name)'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(middle_name)'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', '%' . $inputName . '%');
            });
        }

        $data = $query->where('computer_use', 'Yes')->orderBy(DB::raw('DATE(log_user_logs.timestamp)'), 'asc') // Corrected table name
            ->get();
        return $data;
    }
}
