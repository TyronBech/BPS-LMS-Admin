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
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserLogsController extends Controller
{
    public function index()
    {
        $inputName      = null;
        $fromInputDate  = null;
        $toInputDate    = null;
        $peak_hour      = "00:00";
        $data           = Log::with('users')->orderBy(DB::raw('date(timestamp)'), 'desc')
            ->orderBy(DB::raw('time(timestamp)'), 'desc')->get();
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
        return view('report.users.user-logs', compact('data', 'inputName', 'fromInputDate', 'toInputDate', 'peak_hour'));
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
            return redirect()->route('report.user')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request);
            $this->exportExcel($data);
            return redirect()->route('report.user')->with('toast-success', 'Successfully exported to Excel');
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
        return view('report.users.user-logs', compact('data', 'inputName', 'fromInputDate', 'toInputDate', 'peak_hour'));
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
        $chunk    = $data->chunk(25);
        $arrayPdf = ['data' => $chunk];
        $pdf = Pdf::loadView('pdf.user-pdf-report-format', $arrayPdf);
        return $pdf->download('users-report_' . date('Y-m-d') . '.pdf');
    }
    private function exportExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $headers = ['Log ID', 'RFID', 'Name', 'Date', 'Time', 'Compute Use', 'Action'];
        $columnLetters = range('A', 'G');

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
        }

        // Fill data rows
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->id);
            $sheet->setCellValue('B' . $row, $item->users->rfid);
            $sheet->setCellValue('C' . $row, $item->users->last_name . ', ' . $item->users->first_name . ' ' . $item->users->middle_name);
            $sheet->setCellValue('D' . $row, Carbon::parse($item->timestamp)->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, Carbon::parse($item->timestamp)->format('H:i:s'));
            $sheet->setCellValue('F' . $row, $item->computer_use);
            $sheet->setCellValue('G' . $row, $item->action);
            $row++;
        }

        // Generate file and force download
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new WriterXlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="student-report_' . date('Y-m-d') . '.xlsx"',
        ]);
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('first-name'));

        $query = Log::with('users');

        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(user_logs.timestamp)'), [$fromInputDate, $toInputDate]); // Corrected table name
        }

        if (strlen($inputName) > 0) {
            $query->whereHas('users', function ($q) use ($inputName) {
                $q->where(DB::raw('lower(first_name)'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(last_name)'), 'like', '%' . $inputName . '%')
                    ->orWhere(DB::raw('lower(middle_name)'), 'like', '%' . $inputName . '%');
            });
        }

        $data = $query->orderBy(DB::raw('DATE(user_logs.timestamp)'), 'asc') // Corrected table name
            ->get();
        return $data;
    }
}
