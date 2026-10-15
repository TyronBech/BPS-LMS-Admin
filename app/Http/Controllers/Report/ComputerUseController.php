<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log as AppLog;
use App\Models\UISetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ComputerUseController extends Controller
{
    /**
     * Handles the page request for the computer use report.
     *
     * It extracts the search, user type, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, search, user type, start date, end date, peak hour and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $search      = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $peak_hour      = "00:00";
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');

        Log::info('Computer Use Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'search' => $search,
                'user_type' => $userType,
                'start_date' => $fromInputDate,
                'end_date' => $toInputDate,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            Log::warning('Computer Use Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data           = $this->generateData($request, new AppLog(), false);

        // Query consolidated hourly counts for the summary card
        $summaryQuery = AppLog::query()
            ->whereNotNull('time_in')
            ->where('computer_use', 'Yes')
            ->whereHas('user');

        if ($fromInputDate && $toInputDate) {
            $startDate = Carbon::createFromFormat('m/d/Y', $fromInputDate)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $toInputDate)->endOfDay();
            $summaryQuery->whereBetween('time_in', [$startDate, $endDate]);
        }

        if (strlen($search) > 0) {
            $searchTerms = array_filter(explode(' ', $search));
            $summaryQuery->whereHas('user', function ($q) use ($searchTerms) {
                $q->where(function ($sub) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $sub->where(function ($queryWrapper) use ($term) {
                            $queryWrapper->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                        });
                    }
                });
            });
        }

        if ($userType === 'students') {
            $summaryQuery->whereHas('user.students');
        } elseif ($userType === 'employees') {
            $summaryQuery->whereHas('user.employees');
        }

        $counts = $summaryQuery->selectRaw('HOUR(time_in) as log_hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(time_in)'))
            ->get()
            ->pluck('count', 'log_hour')
            ->toArray();

        $hourlySummary = array_fill(6, 16, 0);
        foreach ($counts as $hour => $count) {
            if ($hour >= 6 && $hour <= 21) {
                $hourlySummary[$hour] = $count;
            }
        }

        $numDays = 1;
        if ($fromInputDate && $toInputDate) {
            $startDate = Carbon::createFromFormat('m/d/Y', $fromInputDate)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $toInputDate)->endOfDay();
            $numDays = $startDate->diffInDays($endDate) + 1;
            if ($numDays < 1) $numDays = 1;
        } else {
            $numDays = $summaryQuery->clone()->selectRaw('COUNT(DISTINCT DATE(time_in)) as count')->first()->count ?? 1;
            if ($numDays < 1) $numDays = 1;
        }

        $hours          = $data->map(function ($item) {
            $item = Carbon::parse($item->start)->format('H:i:s');
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
        return view('report.computers.index', compact('data', 'hourlySummary', 'numDays', 'search', 'userType', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage'));
    }
    /**
     * This function is used to handle the search request for the computer use report.
     * It takes in the request object and extracts the search term, user type, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * If the validation fails, it logs a warning message with the user id, errors, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, search term, user type, start date, end date, peak hour and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $search         = $request->input('search', '');
        $userType       = $request->input('user_type', 'students');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $peak_hour      = "00:00";

        Log::info('Computer Use Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['search', 'user_type', 'start', 'end', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'perPage'       => 'nullable|integer|min:1|max:500'
        ]);
        if ($validator->fails()) {
            Log::warning('Computer Use Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->input('submit') == 'pdf') {
            Log::info('Computer Use Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new AppLog(), true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->generatePDF($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Computer Use Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new AppLog(), true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->exportExcel($data);
            return redirect()->route('report.computer-use')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new AppLog(), false);

        // Query consolidated hourly counts for the summary card
        $summaryQuery = AppLog::query()
            ->whereNotNull('time_in')
            ->where('computer_use', 'Yes')
            ->whereHas('user');

        if ($fromInputDate && $toInputDate) {
            $startDate = Carbon::createFromFormat('m/d/Y', $fromInputDate)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $toInputDate)->endOfDay();
            $summaryQuery->whereBetween('time_in', [$startDate, $endDate]);
        }

        if (strlen($search) > 0) {
            $searchTerms = array_filter(explode(' ', $search));
            $summaryQuery->whereHas('user', function ($q) use ($searchTerms) {
                $q->where(function ($sub) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $sub->where(function ($queryWrapper) use ($term) {
                            $queryWrapper->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                        });
                    }
                });
            });
        }

        if ($userType === 'students') {
            $summaryQuery->whereHas('user.students');
        } elseif ($userType === 'employees') {
            $summaryQuery->whereHas('user.employees');
        }

        $counts = $summaryQuery->selectRaw('HOUR(time_in) as log_hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(time_in)'))
            ->get()
            ->pluck('count', 'log_hour')
            ->toArray();

        $hourlySummary = array_fill(6, 16, 0);
        foreach ($counts as $hour => $count) {
            if ($hour >= 6 && $hour <= 21) {
                $hourlySummary[$hour] = $count;
            }
        }

        $numDays = 1;
        if ($fromInputDate && $toInputDate) {
            $startDate = Carbon::createFromFormat('m/d/Y', $fromInputDate)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $toInputDate)->endOfDay();
            $numDays = $startDate->diffInDays($endDate) + 1;
            if ($numDays < 1) $numDays = 1;
        } else {
            $numDays = $summaryQuery->clone()->selectRaw('COUNT(DISTINCT DATE(time_in)) as count')->first()->count ?? 1;
            if ($numDays < 1) $numDays = 1;
        }

        $hours = $data->map(function ($item) {
            $item = Carbon::parse($item->start)->format('H:i:s');
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
        return view('report.computers.index', compact('data', 'hourlySummary', 'numDays', 'search', 'userType', 'fromInputDate', 'toInputDate', 'peak_hour', 'perPage'));
    }
    /**
     * This function takes in an array of times and returns the peak hour.
     * A peak hour is the hour with the highest count of occurrences in the array.
     * If there are no times, it returns "00".
     * It goes through each time in the array, extracts the hour from it, and counts how many times the hour occurs.
     * It then compares each hour's count with the max count and updates the max count and peak hour if necessary.
     * Finally, it returns the peak hour in the format "HH".
     *
     * @param array $times an array of times in the format "HH:MM:SS"
     * @return string the peak hour in the format "HH"
     */
    private function findPeakHour($times)
    {
        $peakHour = null;
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
    /**
     * Generates a PDF report for the online research report.
     *
     * @param Illuminate\Database\Eloquent\Collection $data the data to be included in the report
     */
    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Online Research Report', request('start'), request('end'), $data, 'start'),
            'school'        => $settings->org_name ?? "Bicutan Parochial School, Inc.",
            'address'       => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'logo'          => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
            'user'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date'          => "as of " . date('F j, Y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
            'schoolYear'    => \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'start')
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.computer-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('computer-use-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the computer use report to an Excel file.
     *
     * @param Illuminate\Database\Eloquent\Collection $data the data to be included in the report
     */
    private function exportExcel(Collection $data)
    {
        $spreadsheet    = new Spreadsheet();
        $logo           = new Drawing();
        $settings       = UISetting::first() ?? new UISetting();
        $sheet          = $spreadsheet->getActiveSheet();

        $tempLogoPath = public_path('img/orgLogoFull.png');
        $decodedLogo = base64_decode($settings->org_logo_full);
        file_put_contents($tempLogoPath, $decodedLogo);

        $logo->setName(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo->setDescription(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo->setPath($tempLogoPath ?? public_path('img/BPSLogoFull.png'));
        $logo->setHeight(100);
        $logo->setCoordinates('B1');
        if ($data->first() && $data->first()->user->students) {
            $logo->setOffsetX(20);
        } elseif ($data->first() && $data->first()->user->employees) {
            $logo->setOffsetX(-70);
        }
        $logo->setOffsetY(1);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Computer Use Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        if ($data->first() && $data->first()->user->students) {
            $sheet->mergeCells('A6:E6');
            $sheet->getStyle('A6:E6')->getFont()->setBold(true);
            $sheet->getStyle('A6:E6')->getFont()->setSize(14);
            $sheet->getStyle('A6:E6')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A6:E6')->getAlignment()->setVertical('center');
        } elseif ($data->first() && $data->first()->user->employees) {
            $sheet->mergeCells('A6:D6');
            $sheet->getStyle('A6:D6')->getFont()->setBold(true);
            $sheet->getStyle('A6:D6')->getFont()->setSize(14);
            $sheet->getStyle('A6:D6')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A6:D6')->getAlignment()->setVertical('center');
        } else {
            $sheet->mergeCells('A6:C6');
            $sheet->getStyle('A6:C6')->getFont()->setBold(true);
            $sheet->getStyle('A6:C6')->getFont()->setSize(14);
            $sheet->getStyle('A6:C6')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A6:C6')->getAlignment()->setVertical('center');
        }
        $sheet->setCellValue('A6', \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Online Research Report', request('start'), request('end'), $data, 'start'));

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        if ($data->first() && $data->first()->user->students) {
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->mergeCells('A7:E7');
        } elseif ($data->first() && $data->first()->user->teachers) {
            $sheet->mergeCells('A7:D7');
        }
        $sheet->setCellValue('A7', 'as of ' . date('F j, Y'));
        if ($data->first() && $data->first()->user->students) {
            $sheet->getStyle('A7:E7')->getFont()->setBold(true);
            $sheet->getStyle('A7:E7')->getFont()->setSize(10);
            $sheet->getStyle('A7:E7')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A7:E7')->getAlignment()->setVertical('center');
            $sheet->getStyle('A7:E7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A9:E9')->getFont()->setSize(10);
            $sheet->getStyle('A9:E9')->getFont()->setBold(true);
            $sheet->getStyle('A9:E9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A9:E9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        } elseif ($data->first() && $data->first()->user->employees) {
            $sheet->getStyle('A7:D7')->getFont()->setBold(true);
            $sheet->getStyle('A7:D7')->getFont()->setSize(10);
            $sheet->getStyle('A7:D7')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A7:D7')->getAlignment()->setVertical('center');
            $sheet->getStyle('A7:D7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A9:D9')->getFont()->setSize(10);
            $sheet->getStyle('A9:D9')->getFont()->setBold(true);
            $sheet->getStyle('A9:D9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A9:D9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        }
        $sheet->setCellValue('A9', 'Name');
        if ($data->first() && $data->first()->user->students) {
            $sheet->setCellValue('B9', 'Level');
            $sheet->setCellValue('C9', 'Section');
            $colD = 'D';
            $colE = 'E';
        } elseif ($data->first() && $data->first()->user->employees) {
            $sheet->setCellValue('B9', 'Role');
            $colD = 'C';
            $colE = 'D';
        } else {
            $colD = 'B';
            $colE = 'C';
        }
        $sheet->setCellValue($colD . '9', 'Date');
        $sheet->setCellValue($colE . '9', 'Time');
        $row = 10;
        foreach ($data as $item) {
            if (!$item->user) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            if ($item->user->students) {
                $sheet->setCellValue('B' . $row, $item->user->students->level);
                $sheet->setCellValue('C' . $row, $item->user->students->section);
                $colD = 'D';
                $colE = 'E';
            } elseif ($item->user->employees) {
                $sheet->setCellValue('B' . $row, $item->user->employees->employee_role);
                $colD = 'C';
                $colE = 'D';
            }
            $sheet->setCellValue($colD . $row, Carbon::parse($item->start)->format('M j, Y'));
            $sheet->setCellValue($colE . $row, Carbon::parse($item->start)->format('g:i A'));
            $sheet->getStyle('A' . $row . ':' . $colE . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':' . $colE . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('A' . $row . ':' . $colE . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:' . $colE . ($row - 1))->applyFromArray($styleArray);

        $row += 2;
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':D' . $row;
        $sheet->getStyle($styleRange)->getFont()->setBold(true);
        $sheet->getStyle($styleRange)->getFont()->setSize(10);
        $sheet->getStyle($styleRange)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($styleRange)->getAlignment()->setVertical('left');
        $sheet->getStyle($styleRange)->getAlignment()->setWrapText(true);

        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'computer-use-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }
    /**
     * Generates data for the computer use report.
     *
     * @param Request $request
     * @param AppLog $model
     * @param bool $isExport
     * @return Collection|Illuminate\Pagination\LengthAwarePaginator
     */
    private function generateData(Request $request, AppLog $model, bool $isExport = false)
    {
        $startStr   = $request->input('start');
        $endStr     = $request->input('end');
        $search     = strtolower($request->input('search'));
        $perPage    = $request->input('perPage', 10);
        $userType   = $request->input('user_type', 'students');

        $query = $model->newQuery()
            ->select(['id', 'user_id', 'time_in as start', 'remarks'])
            ->with('user:id,first_name,middle_name,last_name')
            ->where('computer_use', 'Yes');

        if ($userType === 'students') {
            $query->with('user.students:user_id,level,section')
                ->whereHas('user.students');
        } elseif ($userType === 'employees') {
            $query->with('user.employees:user_id,employee_role')
                ->whereHas('user.employees');
        }

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('time_in', [$startDate, $endDate]);
        }

        if (strlen($search) > 0) {
            $searchTerms = array_filter(explode(' ', $search));
            $query->whereHas('user', function ($q) use ($searchTerms) {
                $q->where(function ($sub) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $sub->where(function ($queryWrapper) use ($term) {
                            $queryWrapper->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                        });
                    }
                });
            });
        }

        $query->orderBy('time_in', 'desc')->orderBy('id', 'desc');

        if ($isExport) {
            $data = $query->get();
            $data->reporting_period = \App\Helpers\ReportHelper::buildReportingPeriod($data, 'start');
            return $data->makeHidden(['id', 'user_id']);
        }

        return $query->paginate($perPage)->appends($request->all());
    }

    public function graph(Request $request)
    {
        Log::info('Computer Use Report: Graph data requested', [
            'user_id' => Auth::guard('admin')->id(),
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'user_type' => $request->user_type,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $baseQuery = AppLog::query();
        $baseQuery->whereNotNull('time_in')
            ->where('computer_use', 'Yes')
            ->whereHas('user');

        $userType = $request->input('user_type', 'all');
        if ($userType === 'students') {
            $baseQuery->whereHas('user.students');
        } elseif ($userType === 'employees') {
            $baseQuery->whereHas('user.employees');
        }

        $type = strtolower($request->input('type', 'monthly'));
        $hasCustomDates = $request->start_date && $request->end_date;
        $chartTitle = '';
        $reportingPeriod = '';
        $labels = collect();
        $counts = collect();

        // Step 1: Determine the effective date range
        if ($hasCustomDates) {
            $start = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
            $end = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();
        } else {
            // Compute default date range based on type
            $now = Carbon::now();
            switch ($type) {
                case 'hourly':
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    break;
                case 'daily':
                    $start = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $end = $now->copy()->startOfWeek(Carbon::MONDAY)->addDays(4)->endOfDay();
                    break;
                case 'weekly':
                    $start = $now->copy()->startOfMonth()->startOfDay();
                    $end = $now->copy()->endOfMonth()->endOfDay();
                    break;
                case 'monthly':
                    // Philippine school year: June of current year to March of next year
                    if ($now->month >= 6) {
                        $start = Carbon::create($now->year, 6, 1)->startOfDay();
                        $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                    } else {
                        $start = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                        $end = Carbon::create($now->year, 3, 31)->endOfDay();
                    }
                    break;
                case 'yearly':
                    $startYear = $now->year - 10;
                    $endYear = $now->year;
                    $start = Carbon::create($startYear, 1, 1)->startOfDay();
                    $end = Carbon::create($endYear, 12, 31)->endOfDay();
                    break;
                default:
                    // Default to monthly (school year)
                    if ($now->month >= 6) {
                        $start = Carbon::create($now->year, 6, 1)->startOfDay();
                        $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                    } else {
                        $start = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                        $end = Carbon::create($now->year, 3, 31)->endOfDay();
                    }
                    $type = 'monthly';
                    break;
            }
        }

        // Step 2: Build the base query with the effective date range
        $query = (clone $baseQuery)->whereBetween('time_in', [$start, $end]);

        // Step 3: Format data according to type
        if ($type === 'hourly') {
            // Aggregate hourly totals across the entire date range (8am–5pm)
            $data = $query->selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
                ->whereBetween(DB::raw('HOUR(time_in)'), [8, 17])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->keyBy('hour');

            $hours = range(8, 17);
            $labels = collect($hours)->map(fn($h) => strtolower(Carbon::createFromTime($h)->format('ga')));
            $counts = collect($hours)->map(fn($h) => $data->get($h)->count ?? 0);

            if ($hasCustomDates) {
                if ($start->isSameDay($end->copy()->startOfDay())) {
                    $reportingPeriod = $start->format('F d, Y');
                    $chartTitle = "Online Research Logs for " . $start->format('M d, Y') . " (Hourly)";
                } else {
                    $reportingPeriod = $start->format('F d, Y') . ' to ' . $end->copy()->startOfDay()->format('F d, Y');
                    $chartTitle = "Online Research Logs (Hourly) from " . $start->format('M d, Y') . " to " . $end->copy()->startOfDay()->format('M d, Y');
                }
            } else {
                $reportingPeriod = Carbon::today()->format('F d, Y');
                $chartTitle = "Online Research Logs for " . Carbon::today()->format('M d, Y') . " (Hourly)";
            }
        } elseif ($type === 'daily') {
            // Each day as a data point
            $data = $query->selectRaw('DATE(time_in) as day, COUNT(*) as count')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            if ($hasCustomDates) {
                // Generate labels for each day in the custom range
                $current = $start->copy();
                $endDate = $end->copy()->startOfDay();
                while ($current->lte($endDate)) {
                    $labels->push($current->format('M d'));
                    $counts->push($data->get($current->toDateString())->count ?? 0);
                    $current->addDay();
                }
                $reportingPeriod = $start->format('F d, Y') . ' to ' . $endDate->format('F d, Y');
                $chartTitle = "Online Research Logs (Daily) from " . $start->format('M d, Y') . " to " . $endDate->format('M d, Y');
            } else {
                // Default: Mon-Fri of current week
                $monday = $start->copy();
                $labels = collect(range(0, 4))->map(fn($i) => $monday->copy()->addDays($i)->format('l'));
                $counts = collect(range(0, 4))->map(function ($i) use ($data, $monday) {
                    $d = $monday->copy()->addDays($i)->toDateString();
                    return $data->get($d)->count ?? 0;
                });
                $friday = $monday->copy()->addDays(4);
                $reportingPeriod = $monday->format('F d, Y') . ' to ' . $friday->format('F d, Y');
                $chartTitle = "Online Research Logs for week of " . $monday->format('M d, Y');
            }
        } elseif ($type === 'weekly') {
            // Split range into week buckets (Mon-Fri)
            $dayCounts = $query->selectRaw('DATE(time_in) as day, COUNT(*) as count')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $current = $start->copy();
            $rangeEnd = $end->copy()->startOfDay();
            $weekIndex = 1;

            while ($current->lte($rangeEnd)) {
                // Skip weekends
                if ($current->isSaturday() || $current->isSunday()) {
                    $current->next(Carbon::MONDAY);
                    if ($current->gt($rangeEnd)) break;
                }

                // Determine week end = upcoming Friday (or range end)
                $dayOfWeekIso = $current->dayOfWeekIso;
                $daysToFriday = 5 - $dayOfWeekIso;
                if ($daysToFriday < 0) $daysToFriday = 0;
                $weekEnd = $current->copy()->addDays($daysToFriday);
                if ($weekEnd->gt($rangeEnd)) $weekEnd = $rangeEnd->copy();

                // Sum counts for Mon-Fri in this range
                $sum = 0;
                for ($d = $current->copy(); $d->lte($weekEnd); $d->addDay()) {
                    if ($d->isSaturday() || $d->isSunday()) continue;
                    $sum += $dayCounts->get($d->toDateString())->count ?? 0;
                }

                $label = 'Week ' . $weekIndex . ' (' . $current->format('M j') . ' - ' . $weekEnd->format('M j') . ')';
                $labels->push($label);
                $counts->push($sum);

                $current = $weekEnd->copy()->next(Carbon::MONDAY);
                $weekIndex++;
            }

            if ($hasCustomDates) {
                $reportingPeriod = $start->format('F d, Y') . ' to ' . $end->copy()->startOfDay()->format('F d, Y');
                $chartTitle = "Online Research Logs (Weekly) from " . $start->format('M d, Y') . " to " . $end->copy()->startOfDay()->format('M d, Y');
            } else {
                $now = Carbon::now();
                $reportingPeriod = $now->format('F Y');
                $chartTitle = "Online Research Logs by week for " . $now->format('F Y');
            }
        } elseif ($type === 'monthly') {
            // Group by year+month, ordered by school year (Jun→Mar) when no custom dates
            $data = $query->selectRaw('YEAR(time_in) as year, MONTH(time_in) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Build a keyed map: "YYYY-MM" => count
            $monthlyData = [];
            foreach ($data as $row) {
                $key = $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $monthlyData[$key] = $row->count;
            }

            // Generate labels for each month in the range (in order)
            $y = $start->year;
            $m = $start->month;
            $endY = $end->year;
            $endM = $end->month;

            while ($y < $endY || ($y == $endY && $m <= $endM)) {
                $key = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                $labels->push(Carbon::create($y, $m, 1)->format('F Y'));
                $counts->push($monthlyData[$key] ?? 0);
                
                $m++;
                if ($m > 12) {
                    $m = 1;
                    $y++;
                }
            }

            if ($hasCustomDates) {
                $reportingPeriod = $start->format('F Y') . ' to ' . $end->copy()->startOfMonth()->format('F Y');
                $chartTitle = "Online Research Logs (Monthly) from " . $start->format('F Y') . " to " . $end->copy()->startOfMonth()->format('F Y');
            } else {
                // School year display
                $syStartYear = $start->year;
                $syEndYear = $end->year;
                $reportingPeriod = "S.Y. {$syStartYear}-{$syEndYear} (June {$syStartYear} – March {$syEndYear})";
                $chartTitle = "Online Research Logs (Monthly) — S.Y. {$syStartYear}-{$syEndYear}";
            }
        } elseif ($type === 'yearly') {
            // Group by year
            $data = $query->selectRaw('YEAR(time_in) as year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year')
                ->get()
                ->keyBy('year');

            // Determine unique years in range
            $startYear = $start->year;
            $endYear = $end->year;

            $labels = collect(range($startYear, $endYear))->map(fn($y) => (string) $y);
            $counts = collect(range($startYear, $endYear))->map(fn($y) => $data->get($y)->count ?? 0);

            if ($hasCustomDates) {
                if ($startYear === $endYear) {
                    $reportingPeriod = (string) $startYear;
                    $chartTitle = "Online Research Logs for {$startYear}";
                } else {
                    $reportingPeriod = "{$startYear} to {$endYear}";
                    $chartTitle = "Online Research Logs (Yearly) from {$startYear} to {$endYear}";
                }
            } else {
                $reportingPeriod = "{$startYear} to {$endYear}";
                $chartTitle = "Online Research Logs for the past " . ($endYear - $startYear + 1) . " years ({$startYear} - {$endYear})";
            }
        } else {
            // Fallback: treat as monthly with school year
            $now = Carbon::now();
            if ($now->month >= 6) {
                $start = Carbon::create($now->year, 6, 1)->startOfDay();
                $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
            } else {
                $start = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                $end = Carbon::create($now->year, 3, 31)->endOfDay();
            }
            $query = (clone $baseQuery)->whereBetween('time_in', [$start, $end]);

            $data = $query->selectRaw('YEAR(time_in) as year, MONTH(time_in) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $monthlyData = [];
            foreach ($data as $row) {
                $key = $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $monthlyData[$key] = $row->count;
            }

            $y = $start->year;
            $m = $start->month;
            $endY = $end->year;
            $endM = $end->month;

            while ($y < $endY || ($y == $endY && $m <= $endM)) {
                $key = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                $labels->push(Carbon::create($y, $m, 1)->format('F Y'));
                $counts->push($monthlyData[$key] ?? 0);
                
                $m++;
                if ($m > 12) {
                    $m = 1;
                    $y++;
                }
            }

            $syStartYear = $start->year;
            $syEndYear = $end->year;
            $reportingPeriod = "S.Y. {$syStartYear}-{$syEndYear} (June {$syStartYear} – March {$syEndYear})";
            $chartTitle = "Online Research Logs (Monthly) — S.Y. {$syStartYear}-{$syEndYear}";
        }

        return response()->json([
            'labels'           => $labels->values(),
            'counts'           => $counts->values(),
            'chart_title'      => \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Online Research', $start, $end),
            'reporting_period' => ''
        ]);
    }

    public function exportGraph(Request $request)
    {
        Log::info('Computer Use Report: Graph export requested', [
            'user_id' => Auth::guard('admin')->id(),
            'type' => $request->input('type'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        try {
            $chart = $request->input('chart');
            $type  = strtolower((string) $request->input('type', ''));
            $start = $request->input('start_date');
            $end   = $request->input('end_date');
            $userType = $request->input('user_type', 'all');

            $validator = Validator::make($request->all(), [
                'type'          => 'nullable|in:hourly,daily,weekly,monthly,yearly',
                'start_date'    => 'nullable|date_format:m/d/Y|required_with:end_date',
                'end_date'      => 'nullable|date_format:m/d/Y|required_with:start_date|after_or_equal:start_date',
                'user_type'     => 'nullable|in:all,students,employees',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            
            // build range string using same defaults as graph()
            $range = '';
            $startDate = null;
            $endDate = null;
            $hasCustomDates = $start && $end;

            if ($hasCustomDates) {
                // Custom date range
                $startDate = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
                if ($startDate->isSameDay($endDate)) {
                    $range = $startDate->format('F d, Y');
                } else {
                    $range = 'from ' . $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y');
                }
            } else {
                $now = Carbon::now();
                if (empty($type)) $type = 'monthly';
                switch ($type) {
                    case 'hourly':
                        $startDate = Carbon::today()->startOfDay();
                        $endDate = Carbon::today()->endOfDay();
                        $range = Carbon::today()->format('F d, Y');
                        break;
                    case 'daily':
                        $startDate = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                        $endDate = $now->copy()->startOfWeek(Carbon::MONDAY)->addDays(4)->endOfDay();
                        $range = 'from ' . $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y');
                        break;
                    case 'weekly':
                        $startDate = $now->copy()->startOfMonth()->startOfDay();
                        $endDate = $now->copy()->endOfMonth()->endOfDay();
                        $range = $now->format('F Y');
                        break;
                    case 'monthly':
                        if ($now->month >= 6) {
                            $startDate = Carbon::create($now->year, 6, 1)->startOfDay();
                            $endDate = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                        } else {
                            $startDate = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                            $endDate = Carbon::create($now->year, 3, 31)->endOfDay();
                        }
                        $syStart = $startDate->year;
                        $syEnd = $endDate->year;
                        $range = "S.Y. {$syStart}-{$syEnd} (June {$syStart} – March {$syEnd})";
                        break;
                    case 'yearly':
                        $startYear = $now->year - 10;
                        $endYear = $now->year;
                        $startDate = Carbon::create($startYear, 1, 1)->startOfDay();
                        $endDate = Carbon::create($endYear, 12, 31)->endOfDay();
                        $range = "{$startYear} to {$endYear}";
                        break;
                    default:
                        if ($now->month >= 6) {
                            $startDate = Carbon::create($now->year, 6, 1)->startOfDay();
                            $endDate = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                        } else {
                            $startDate = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                            $endDate = Carbon::create($now->year, 3, 31)->endOfDay();
                        }
                        $syStart = $startDate->year;
                        $syEnd = $endDate->year;
                        $range = "S.Y. {$syStart}-{$syEnd} (June {$syStart} – March {$syEnd})";
                        break;
                }
            }

            // Fetch hourly summary data for PDF export (Page 2)
            $dateQuery = AppLog::query()
                ->whereNotNull('time_in')
                ->where('computer_use', 'Yes')
                ->whereHas('user')
                ->whereBetween('time_in', [$startDate, $endDate]);

            if ($userType === 'students') {
                $dateQuery->whereHas('user.students');
            } elseif ($userType === 'employees') {
                $dateQuery->whereHas('user.employees');
            }

            $dates = $dateQuery->selectRaw('DATE(time_in) as log_date')
                ->groupBy(DB::raw('DATE(time_in)'))
                ->orderBy(DB::raw('DATE(time_in)'), 'asc')
                ->get()
                ->pluck('log_date')
                ->toArray();

            $hourlyData = [];
            if (!empty($dates)) {
                $countsQuery = AppLog::query()
                    ->whereNotNull('time_in')
                    ->where('computer_use', 'Yes')
                    ->whereHas('user')
                    ->whereIn(DB::raw('DATE(time_in)'), $dates);

                if ($userType === 'students') {
                    $countsQuery->whereHas('user.students');
                } elseif ($userType === 'employees') {
                    $countsQuery->whereHas('user.employees');
                }

                $counts = $countsQuery->selectRaw('DATE(time_in) as log_date, HOUR(time_in) as log_hour, COUNT(*) as count')
                    ->groupBy(DB::raw('DATE(time_in)'), DB::raw('HOUR(time_in)'))
                    ->get();

                foreach ($dates as $date) {
                    $formattedDate = Carbon::parse($date)->format('F j, Y');
                    $hourlyData[$formattedDate] = array_fill(6, 16, 0);
                }

                foreach ($counts as $c) {
                    $formattedDate = Carbon::parse($c->log_date)->format('F j, Y');
                    $hour = (int)$c->log_hour;
                    if ($hour >= 6 && $hour <= 21) {
                        $hourlyData[$formattedDate][$hour] = $c->count;
                    }
                }
            }

            $settings = UISetting::first() ?? new UISetting();
            $items = [
                'title'       => \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Online Research Graphical Report', $start ?? $startDate, $end ?? $endDate, $hourlyData, 'start'),
                'school'      => $settings->org_name ?? "Bicutan Parochial School, Inc.",
                'address'     => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
                'logo'        => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
                'user'        => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'date'        => now()->format('F d, Y'),
                'chart'       => $chart,
                'range'       => $range,
                'hourlyData'  => $hourlyData,
                'settings'    => $settings,
                'schoolYear'  => \App\Helpers\ReportHelper::getSchoolYear($startDate, $endDate, $hourlyData, 'start')
            ];

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);

            $pdf = new Dompdf($options);
            $pdf->setPaper('legal', 'landscape');
            $pdf->loadHtml(view('pdf.computer-graph-pdf-report', $items)->render());
            $pdf->render();

            $output = $pdf->output();
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="computer-use-graph-' . date('Y-m-d') . '.pdf"');
        } catch (\Exception $e) {
            Log::error('Computer Use Report: PDF generation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
