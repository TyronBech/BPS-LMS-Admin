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
    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $userType       = $request->input('user_type', 'all');
        $peak_hour      = "00:00";
        $perPage        = $request->input('perPage', 10);
        $data           = $this->generateData($request, new Log(), false);
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
        return view('report.users.user-logs', compact('data', 'search', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage', 'userType'));
    }
    public function search(Request $request)
    {
        $search         = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $userType       = $request->input('user_type', 'all');
        $peak_hour      = "00:00";
        $perPage        = $request->input('perPage', 10);
        $tableName      = new Log();
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date',
            'search'        => 'nullable',
            'perPage'       => 'nullable|numeric|in:10,25,50',
            'user_type'     => 'in:all,student,employee,visitor',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, $tableName, true);
            $this->generatePDF($data);
            return redirect()->route('report.user')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, $tableName, true);
            $this->exportExcel($data);
            return redirect()->route('report.user')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, $tableName, false);
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
        return view('report.users.user-logs', compact('data', 'search', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage', 'userType'));
    }
    public function graph(Request $request)
    {
        $query = Log::query();
        $query->whereNotNull('time_in')
            ->where('computer_use', 'No');

        if ($request->start_date && !$request->end_date) {
            return redirect()->back()->with('toast-warning', 'Please select an end date.');
        }

        $chartTitle = '';
        $labels = collect();
        $counts = collect();

        // --- Date filtering & chart title ---
        if ($request->start_date && $request->end_date) {
            $start = Carbon::createFromFormat('m/d/Y', $request->start_date);
            $end   = Carbon::createFromFormat('m/d/Y', $request->end_date);

            $query->whereBetween(DB::raw('DATE(time_in)'), [
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            ]);

            $chartTitle = "User Logs from {$start->format('M d, Y')} to {$end->format('M d, Y')}";

            // --- Custom aggregation ---
            $data = $query->selectRaw('DATE(time_in) as day, COUNT(*) as count')
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $labels = $data->pluck('day')->map(fn($d) => Carbon::parse($d)->format('M d'));
            $counts = $data->pluck('count');
        } elseif ($request->type === 'daily') {
            $today = Carbon::today();
            $query->whereDate('time_in', $today);

            $chartTitle = "User Logs for " . $today->format('M d, Y');

            $data = $query->selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
                ->whereRaw('HOUR(time_in) BETWEEN 7 AND 17')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $labels = collect(range(7, 17))->map(fn($h) => Carbon::createFromTime($h)->format('h A'));
            $counts = collect(range(7, 17))->map(fn($h) => optional($data->firstWhere('hour', $h))->count ?? 0);
        } elseif ($request->type === 'weekly') {
            $monday = Carbon::now()->startOfWeek();
            $friday = $monday->copy()->addDays(4);

            $query->whereBetween('time_in', [$monday, $friday->endOfDay()]);

            $chartTitle = "User Logs from {$monday->format('M d, Y')} to {$friday->format('M d, Y')}";

            $data = $query->selectRaw('DATE(time_in) as day, COUNT(*) as count')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $labels = collect(range(0, 4))->map(fn($i) => $monday->copy()->addDays($i)->format('D'));
            $counts = collect(range(0, 4))->map(fn($i) => $data->get($monday->copy()->addDays($i)->toDateString())->count ?? 0);
        } elseif ($request->type === 'monthly') {
            $now = Carbon::now();
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();

            $query->whereBetween('time_in', [$start, $end]);

            $chartTitle = "User Logs for the month of " . $now->format('F');

            $data = $query->selectRaw('DATE(time_in) as day, COUNT(*) as count')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $labels->push($date->format('M d'));
                $counts->push($data->get($date->toDateString())->count ?? 0);
            }
        } elseif ($request->type === 'yearly') {
            $now = Carbon::now();
            $startYear = $now->year - 9;
            $endYear   = $now->year;

            $query->whereBetween('time_in', [
                Carbon::create($startYear, 1, 1)->startOfDay(),
                Carbon::create($endYear, 12, 31)->endOfDay()
            ]);

            $data = $query->selectRaw('YEAR(time_in) as year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year')
                ->get()
                ->keyBy('year');

            $labels = collect(range($startYear, $endYear));
            $counts = $labels->map(fn($y) => $data->get($y)->count ?? 0);

            $chartTitle = "User Logs for the past 10 years ({$startYear} - {$endYear})";
        } else {
            // Default fallback → Daily
            $today = Carbon::today();
            $query->whereDate('time_in', $today);

            $chartTitle = "User Logs for " . $today->format('M d, Y');

            $data = $query->selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
                ->whereRaw('HOUR(time_in) BETWEEN 7 AND 17')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $labels = collect(range(7, 17))->map(fn($h) => Carbon::createFromTime($h)->format('h A'));
            $counts = collect(range(7, 17))->map(fn($h) => optional($data->firstWhere('hour', $h))->count ?? 0);
        }

        return response()->json([
            'labels'      => $labels,
            'counts'      => $counts,
            'chart_title' => $chartTitle
        ]);
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
    public function exportGraph(Request $request)
    {
        try {
            $chart = $request->input('chart');
            $type  = strtolower($request->input('type')); // daily, weekly, monthly
            $start = $request->input('start_date');
            $end   = $request->input('end_date');

            $validator = Validator::make($request->all(), [
                'type'          => 'nullable|in:daily,weekly,monthly',
                'start_date'    => 'nullable|date|required_with:end_date',
                'end_date'      => 'nullable|date|required_with:start_date|after_or_equal:start_date',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            // build range string
            $range = '';
            if ($start && $end) {
                // custom date range
                $range = 'from ' . Carbon::parse($start)->format('F d, Y') . ' to ' . Carbon::parse($end)->format('F d, Y');
            } elseif ($type === 'daily') {
                // today
                $range = Carbon::today()->format('F d, Y');
            } elseif ($type === 'weekly') {
                // from Monday to today (but cap at Friday if weekend)
                $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $today  = Carbon::now();

                if ($today->isSaturday() || $today->isSunday()) {
                    $friday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4); // Friday
                    $range  = 'from ' . $monday->format('F d, Y') . ' to ' . $friday->format('F d, Y');
                } else {
                    $range = 'from ' . $monday->format('F d, Y') . ' to ' . $today->format('F d, Y');
                }
            } elseif ($type === 'monthly') {
                // current month + year
                $range = Carbon::now()->format('F Y');
            }

            $items = [
                'title'   => 'Attendance Monitoring Report Graph',
                'school'  => "Bicutan Parochial School, Inc.",
                'address' => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
                'logo'    => base64_encode(file_get_contents(public_path('img/BPSLogoFull.png'))),
                'user'    => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'date'    => now()->format('F d, Y'),
                'chart'   => $chart,
                'range'   => $range
            ];

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);

            $pdf = new Dompdf($options);
            $pdf->setPaper('A4', 'landscape');
            $pdf->loadHtml(view('pdf.user-graph-pdf-report', $items)->render());
            $pdf->render();

            $output = $pdf->output();
            //session()->flash('toast-success', 'Your data has been saved successfully!');
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="user-logs-graph-' . date('Y-m-d') . '.pdf"');
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function generatePDF($data)
    {
        $items = [
            'title'         => 'Attendance Monitoring Report',
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

        $sheet->setTitle('Attendance Monitoring Report');
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
            if (!$item->user) {
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
    private function generateData(Request $request, Log $tableName, bool $isExport = false)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $search         = strtolower($request->input('search', ''));
        $userType       = $request->input('user_type', 'all');
        $perPage        = $request->input('perPage', 10);

        $query = Log::with('user')->whereHas('user')->where("computer_use", "No")->whereNotNull('time_in');

        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $start = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $end = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.time_in)'), [$start, $end]);
        }

        if (strlen($search) > 0) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(DB::raw('lower(first_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(last_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(middle_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', '%' . $search . '%');
            });
        }
        if($userType != 'all') {
            $query->whereHas('user', function ($q) use ($userType) {
                $q->whereHas('privileges', function ($q2) use ($userType) {
                    if ($userType == 'student') {
                        $q2->where('user_type', 'student');
                    } else if ($userType == 'employee') {
                        $q2->where('user_type', 'employee');
                    } else if ($userType == 'visitor') {
                        $q2->where('user_type', 'visitor');
                    }
                });
            });
        }
        if ($isExport) {
            $data = $query->orderBy(DB::raw('DATE(' . $tableName->getTable() . '.time_in)'), 'asc')
                ->orderBy(DB::raw('TIME(' . $tableName->getTable() . '.time_in)'), 'asc')
                ->get();
            $minDate = $data->min(fn($item) => \Carbon\Carbon::parse($item->time_in));
            $maxDate = $data->max(fn($item) => \Carbon\Carbon::parse($item->time_in));
            if ($minDate && $maxDate) {
                $data->reporting_period = $minDate->format('F j, Y') . ' to ' . $maxDate->format('F j, Y');
            } else {
                $data->reporting_period = 'N/A';
            }
        } else {
            $data = $query->orderBy(DB::raw('DATE(' . $tableName->getTable() . '.time_in)'), 'desc')
                ->orderBy(DB::raw('TIME(' . $tableName->getTable() . '.time_in)'), 'desc')
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'search' => $search,
                    'start' => $fromInputDate,
                    'end' => $toInputDate,
                    'user_type' => $userType,
                ]);
        }
        return $data;
    }
}
