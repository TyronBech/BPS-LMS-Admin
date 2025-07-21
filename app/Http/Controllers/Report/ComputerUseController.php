<?php

namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Log; // Import the Log model
use Illuminate\Support\Facades\DB; // Import the DB facade
use Carbon\Carbon; // Import the Carbon class
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use DateTime; // Import the DateTime class
use Dompdf\Dompdf; // Import the Dompdf class
use Dompdf\Options; // Import the Dompdf Options class
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Import the Spreadsheet class
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx; // Import the WriterXlsx class with alias
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // Import the Drawing class
use Illuminate\Support\Facades\Auth; // Import the Auth facade

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
                ->orderBy(DB::raw('DATE(time_in)'), 'desc')
                ->orderBy(DB::raw('TIME(time_in)'), 'desc')
                ->get();
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
            'title'         => 'Users Report',
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
        $dompdf->loadHtml(view('pdf.computer-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('computer-use-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
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
        $logo->setCoordinates('B1');
        $logo->setOffsetX(20);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Computer Use Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->mergeCells('A7:E7');
        $sheet->mergeCells('A8:E8');
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:E8')->getFont()->setBold(true);
        $sheet->getStyle('A7:E8')->getFont()->setSize(10);
        $sheet->getStyle('A7:E8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:E8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:E8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:E10')->getFont()->setSize(12);
        $sheet->getStyle('A10:E10')->getFont()->setBold(true);
        $sheet->setCellValue('A10', 'Name');
        $sheet->setCellValue('B10', 'Level');
        $sheet->setCellValue('C10', 'Section');
        $sheet->setCellValue('D10', 'Date');
        $sheet->setCellValue('E10', 'Time');
        $row = 11;
        foreach ($data as $item) {
            if(!$item->user) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            $sheet->setCellValue('B' . $row, $item->user->students->level);
            $sheet->setCellValue('C' . $row, $item->user->students->section);
            $sheet->setCellValue('D' . $row, Carbon::parse($item->time_in)->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, Carbon::parse($item->time_in)->format('g:i A'));
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'computer-use-report ' . date('Y-m-d') . '.xlsx';
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

        $query = Log::with(['user.students'])->whereHas('user.students');

        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(log_user_logs.time_in)'), [$fromInputDate, $toInputDate]); // Corrected table name
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

        $data = $query->where('computer_use', 'Yes')->orderBy(DB::raw('DATE(log_user_logs.time_in)'), 'asc') // Corrected table name
            ->get();
        return $data;
    }
}
