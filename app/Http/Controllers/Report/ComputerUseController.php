<?php

namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Auth;

class ComputerUseController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $peak_hour      = "00:00";
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');
        $data           = $this->generateData($request, new Log(), false);
        $hours          = $data->map(function ($item) {
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
        return view('report.computers.index', compact('data', 'search', 'userType', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage'));
    }
    public function search(Request $request)
    {
        $search         = $request->input('search' , '');
        $userType       = $request->input('user_type', 'students');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $peak_hour      = "00:00";
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date',
            'search'        => 'nullable',
            'perPage'       => 'nullable|numeric|in:10,25,50'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, new Log(), true);
            $this->generatePDF($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, new Log(), true);
            $this->exportExcel($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new Log(), false);
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
        return view('report.computers.index', compact('data', 'search', 'userType', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage'));
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
            'title'         => 'Online Research Report',
            'school'        => "Bicutan Parochial School, Inc.",
            'address'       => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'logo'          => base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
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
        if($data->first() && $data->first()->user->students) {
            $logo->setOffsetX(20);
        } elseif($data->first() && $data->first()->user->employees) {
            $logo->setOffsetX(5);
        }
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Computer Use Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        if($data->first() && $data->first()->user->students) {
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->mergeCells('A7:E7');
            $sheet->mergeCells('A8:E8');
        } elseif($data->first() && $data->first()->user->teachers) {
            $sheet->mergeCells('A7:D7');
            $sheet->mergeCells('A8:D8');  
        }
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        if($data->first() && $data->first()->user->students) {
            $sheet->getStyle('A7:E8')->getFont()->setBold(true);
            $sheet->getStyle('A7:E8')->getFont()->setSize(10);
            $sheet->getStyle('A7:E8')->getAlignment()->setHorizontal('left');
            $sheet->getStyle('A7:E8')->getAlignment()->setVertical('left');
            $sheet->getStyle('A7:E8')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A10:E10')->getFont()->setSize(12);
            $sheet->getStyle('A10:E10')->getFont()->setBold(true);
        } elseif($data->first() && $data->first()->user->employees) {
            $sheet->getStyle('A7:D8')->getFont()->setBold(true);
            $sheet->getStyle('A7:D8')->getFont()->setSize(10);
            $sheet->getStyle('A7:D8')->getAlignment()->setHorizontal('left');
            $sheet->getStyle('A7:D8')->getAlignment()->setVertical('left');
            $sheet->getStyle('A7:D8')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A10:D10')->getFont()->setSize(12);
            $sheet->getStyle('A10:D10')->getFont()->setBold(true);
        }
        $sheet->setCellValue('A10', 'Name');
        if($data->first() && $data->first()->user->students) {
            $sheet->setCellValue('B10', 'Level');
            $sheet->setCellValue('C10', 'Section');
            $colD = 'D';
            $colE = 'E';
        } elseif($data->first() && $data->first()->user->employees) {
            $sheet->setCellValue('B10', 'Role');
            $colD = 'C';
            $colE = 'D';
        } else {
            $colD = 'B';
            $colE = 'C';
        }
        $sheet->setCellValue($colD . '10', 'Date');
        $sheet->setCellValue($colE . '10', 'Time');
        $row = 11;
        foreach ($data as $item) {
            if(!$item->user) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            if($item->user->students) {
                $sheet->setCellValue('B' . $row, $item->user->students->level);
                $sheet->setCellValue('C' . $row, $item->user->students->section);
                $colD = 'D';
                $colE = 'E';
            } elseif($item->user->employees) {
                $sheet->setCellValue('B' . $row, $item->user->employees->employee_role);
                $colD = 'C';
                $colE = 'D';
            }
            $sheet->setCellValue($colD . $row, Carbon::parse($item->time_in)->format('Y-m-d'));
            $sheet->setCellValue($colE . $row, Carbon::parse($item->time_in)->format('g:i A'));
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'computer-use-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit;
    }
    private function generateData(Request $request, Log $tableName, bool $isExport = false)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $search         = strtolower($request->input('search'));
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');

        $query = $tableName::with(['user.students', 'user.employees'])
            ->where('computer_use', 'Yes');

        $searchFilter = function ($q) use ($search) {
            if (strlen($search) > 0) {
                $q->where(function ($query) use ($search) {
                    $query->where(DB::raw('lower(first_name)'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(last_name)'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(middle_name)'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', '%' . $search . '%');
                });
            }
        };

        if ($userType === 'students') {
            $query->whereHas('user', function ($q) use ($searchFilter) {
                $q->has('students');
                $searchFilter($q);
            });
        } elseif ($userType === 'employees') {
            $query->whereHas('user', function ($q) use ($searchFilter) {
                $q->has('employees');
                $searchFilter($q);
            });
        }

        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $start = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $end = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.time_in)'), [$start, $end]);
        }

        if ($isExport) {
            $data = $query->orderBy($tableName->getTable() . '.time_in', 'desc')
            ->orderBy($tableName->getTable() . '.id', 'desc')->get();
            $minDate = $data->min(fn($item) => \Carbon\Carbon::parse($item->time_in));
            $maxDate = $data->max(fn($item) => \Carbon\Carbon::parse($item->time_in));
            if ($minDate && $maxDate) {
                $data->reporting_period = $minDate->format('F j, Y') . ' to ' . $maxDate->format('F j, Y');
            } else {
                $data->reporting_period = 'N/A';
            }
        } else {
            $data = $query->orderBy(DB::raw($tableName->getTable() . '.time_in'), 'desc')
            ->orderBy(DB::raw($tableName->getTable() . '.id'), 'desc')
            ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'search' => $search,
                    'user_type' => $userType,
                    'start' => $fromInputDate,
                    'end' => $toInputDate,
                ]);
        }
        return $data;
    }
}
