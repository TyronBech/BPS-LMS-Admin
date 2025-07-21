<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserLogsController extends Controller
{
    public function index()
    {
        $inputName      = null;
        $fromInputDate  = null;
        $toInputDate    = null;
        $peak_hour      = "00:00";
        $data           = Log::with('user')->orderBy(DB::raw('date(time_in)'), 'desc')
            ->orderBy(DB::raw('time(time_in)'), 'desc')->get();
        $hours = $data->map(function ($item) {
            $item = Carbon::parse($item->time_in)->format('H:i:s');
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
            $item = Carbon::parse($item->time_in)->format('H:i:s');
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
        $items = [
            'title'         => 'User Logs Report',
            'school'        => "Bicutan Parochial School, Inc.",
            'address'       => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'user'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date'          => date('F j, Y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.user-pdf-report-format', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('users-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $logo           = new Drawing();
        $sheet          = $spreadsheet->getActiveSheet();
        
        $logo->setName('BPS Logo');
        $logo->setDescription('BPS Logo');
        $logo->setPath(public_path('img/BPSLogoFull.png'));
        $logo->setHeight(80);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(70);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Users Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->mergeCells('A7:D7');
        $sheet->mergeCells('A8:D8');
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:D8')->getFont()->setBold(true);
        $sheet->getStyle('A7:D8')->getFont()->setSize(10);
        $sheet->getStyle('A7:D8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:D8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:D8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:D10')->getFont()->setSize(12);
        $sheet->getStyle('A10:D10')->getFont()->setBold(true);
        $sheet->setCellValue('A10', 'Name');
        $sheet->setCellValue('B10', 'Date');
        $sheet->setCellValue('C10', 'Time in');
        $sheet->setCellValue('D10', 'Time out');
        $row = 11;
        foreach ($data as $item) {
            if(!$item->user) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            $sheet->setCellValue('B' . $row, Carbon::parse($item->time_in)->format('Y-m-d'));
            $sheet->setCellValue('C' . $row, Carbon::parse($item->time_in)->format('g:i A'));
            if ($item->time_out) {
                $sheet->setCellValue('D' . $row, Carbon::parse($item->time_out)->format('g:i A'));
            } else {
                $sheet->setCellValue('D' . $row, 'N/A');
            }
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'users-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit;
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('first-name'));

        $query = Log::with('user');

        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(log_user_logs.time_in)'), [$fromInputDate, $toInputDate]); // Corrected table name
        }

        if (strlen($inputName) > 0) {
            $query->whereHas('user', function ($q) use ($inputName) {
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

        $data = $query->orderBy(DB::raw('DATE(log_user_logs.time_in)'), 'asc') // Corrected table name
            ->get();
        return $data;
    }
}
