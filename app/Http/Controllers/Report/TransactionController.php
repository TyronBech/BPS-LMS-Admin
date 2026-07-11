<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\UISetting;
use App\Models\SubjectAccessCode;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;

class TransactionController extends Controller
{
    /**
     * Handles the page request for the transaction report.
     *
     * It extracts the start date, end date, search, type and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, search, start date, end date, type, page size and availability.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $search         = $request->input('search', '');
        $perPage        = $request->input('perPage', 10);
        $type           = $request->input('type', 'All');
        $subjectId      = $request->input('subject_id', 'All');
        $userType       = $request->input('user_type', 'student');
        $availability   = $this->extract_enums((new Transaction())->getTable(), 'transaction_type');

        Log::info('Transaction Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'start_date' => $fromInputDate,
                'end_date' => $toInputDate,
                'search' => $search,
                'type' => $type,
                'user_type' => $userType,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'type'          => 'nullable|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'user_type'     => 'nullable|in:student,employee',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->route('report.circulation')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Transaction(), false);
        $fullData = $this->generateData($request, new Transaction(), true);
        $chartData = $this->getChartData($fullData, $fromInputDate, $toInputDate, $userType);
        $chartLabels = $chartData['labels'];
        $chartCounts = $chartData['counts'];

        $subjects = SubjectAccessCode::orderBy('access_code')->get();
        return view('report.transactions.transactions', compact('data', 'search', 'fromInputDate', 'toInputDate', 'type', 'perPage', 'availability', 'subjects', 'subjectId', 'userType', 'chartLabels', 'chartCounts'));
    }
    /**
     * Handles the search request for the transaction report.
     *
     * It takes in the request object and extracts the search term, start date, end date, type and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * If the validation fails, it logs a warning message with the user id, errors, ip address and timestamp.
     * If the submit button is 'pdf', it generates the PDF export.
     * If the submit button is 'excel', it generates the Excel export.
     * Finally, it generates the data for the report and returns the view with the data, search term, start date, end date, type, page size and availability.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $search         = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $type           = $request->input('type', 'All');
        $subjectId      = $request->input('subject_id', 'All');
        $userType       = $request->input('user_type', 'student');
        $availability   = $this->extract_enums((new Transaction())->getTable(), 'transaction_type');
        $perPage        = $request->input('perPage', 10);

        Log::info('Transaction Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['start', 'end', 'search', 'type', 'perPage', 'user_type']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'type'          => 'nullable|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'user_type'     => 'nullable|in:student,employee',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->input('submit') == 'pdf') {
            Log::info('Transaction Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Transaction(), true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $chartBase64 = $request->input('chart');
            if ($chartBase64) {
                $chartBase64 = str_replace(' ', '+', $chartBase64);
            }
            $this->generatePDF($data, $type, $userType, $chartBase64);
            return redirect()->route('report.circulation')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Transaction Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Transaction(), true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->exportExcel($data, $type, $userType);
            return redirect()->route('report.circulation')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new Transaction(), false);
        $fullData = $this->generateData($request, new Transaction(), true);
        $chartData = $this->getChartData($fullData, $fromInputDate, $toInputDate, $userType);
        $chartLabels = $chartData['labels'];
        $chartCounts = $chartData['counts'];

        $subjects = SubjectAccessCode::orderBy('access_code')->get();
        return view('report.transactions.transactions', compact('data', 'search', 'fromInputDate', 'toInputDate', 'type', 'perPage', 'availability', 'subjects', 'subjectId', 'userType', 'chartLabels', 'chartCounts'));
    }
    /**
     * Generates a PDF report for the transaction report.
     *
     * It takes in the data to be included in the report and the type of report to be generated.
     * The report includes the title, school name, type, logo, address, user name, date, data and total count.
     * The PDF report is then streamed to the browser with the filename 'transaction-report <date>.pdf'.
     *
     * @param Collection $data The data to be included in the report.
     * @param string $type The type of report to be generated.
     * @param string $userType The type of user to filter.
     * @param string|null $chart The base64-encoded PNG image of the chart.
     */
    private function generatePDF(Collection $data, string $type, string $userType, ?string $chart = null)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Report', request('start'), request('end'), $data, 'date_borrowed'),
            'school'        => $settings->org_name ?? "Bicutan Parochial School, Inc.",
            'type'          => $type,
            'userType'      => $userType,
            'logo'          => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
            'address'       => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'user'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date'          => "as of " . date('F j, Y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
            'schoolYear'    => \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'date_borrowed'),
            'chart'         => $chart
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.transaction-pdf-report', $items));
        $dompdf->setPaper('legal', 'landscape');
        $dompdf->render();
        $dompdf->stream('transaction-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }

    /**
     * Calculates the monthly borrowed book counts in PHP to pass as chartLabels and chartCounts to the view.
     *
     * @param Collection $data
     * @param string|null $startStr
     * @param string|null $endStr
     * @param string $userType
     * @return array
     */
    private function getChartData(Collection $data, $startStr, $endStr, string $userType)
    {
        $startDate = null;
        $endDate   = null;

        if ($startStr && $endStr) {
            try {
                $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
                $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            } catch (\Throwable $e) {}
        }

        if (empty($startDate) || empty($endDate)) {
            $schoolYear = \App\Helpers\ReportHelper::getSchoolYear($startStr, $endStr, $data, 'date_borrowed');
            if ($schoolYear && preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
                list($syStart, $syEnd) = explode('-', $schoolYear);
                $startDate = Carbon::create((int)$syStart, 6, 1)->startOfDay();
                $endDate = Carbon::create((int)$syEnd, 3, 31)->endOfDay();
            } else {
                $now = Carbon::now();
                if ($now->month >= 6) {
                    $startDate = Carbon::create($now->year, 6, 1)->startOfDay();
                    $endDate = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                } else {
                    $startDate = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                    $endDate = Carbon::create($now->year, 3, 31)->endOfDay();
                }
            }
        }

        $monthsList = [];
        $current = $startDate->copy()->startOfMonth();
        $endMonth = $endDate->copy()->startOfMonth();

        $maxMonths = 60;
        $countMonths = 0;
        while ($current->lte($endMonth) && $countMonths < $maxMonths) {
            $monthsList[] = [
                'label' => $current->format('F Y'),
                'month' => $current->month,
                'year' => $current->year,
                'count' => 0,
            ];
            $current->addMonth();
            $countMonths++;
        }

        foreach ($data as $item) {
            if ($item->date_borrowed) {
                $borrowedDate = Carbon::parse($item->date_borrowed);
                $mNum = $borrowedDate->month;
                $yNum = $borrowedDate->year;

                foreach ($monthsList as &$m) {
                    if ($m['month'] == $mNum && $m['year'] == $yNum) {
                        if ($userType === 'student' && $item->user && $item->user->students) {
                            $m['count']++;
                        } elseif ($userType === 'employee' && $item->user && $item->user->employees) {
                            $m['count']++;
                        }
                        break;
                    }
                }
            }
        }

        $labels = [];
        $counts = [];
        foreach ($monthsList as $m) {
            $labels[] = $m['label'];
            $counts[] = $m['count'];
        }

        return [
            'labels' => $labels,
            'counts' => $counts
        ];
    }
    /**
     * Exports the transaction report to an Excel file.
     *
     * @param  Illuminate\Database\Eloquent\Collection  $data  The data to be included in the report.
     * @param  string  $type  The type of report to be generated.
     *
     * @return void
     */
    private function exportExcel(Collection $data, string $type, string $userType)
    {
        $spreadsheet    = new Spreadsheet();
        $settings       = UISetting::first() ?? new UISetting();

        $tempLogoPath = public_path('img/orgLogoFull.png');
        $decodedLogo = base64_decode($settings->org_logo_full);
        file_put_contents($tempLogoPath, $decodedLogo);

        // ----------------------------------------------------
        // SHEET 1: SUMMARY
        // ----------------------------------------------------
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');
        $summarySheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $summarySheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $summarySheet->getPageSetup()->setFitToWidth(1);
        $summarySheet->getPageSetup()->setFitToHeight(0);

        // Add logo to Summary Sheet
        $logo1 = new Drawing();
        $logo1->setName(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo1->setDescription(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo1->setPath($tempLogoPath ?? public_path('img/BPSLogoFull.png'));
        $logo1->setHeight(100);
        $logo1->setCoordinates('A1');
        $logo1->setOffsetX(200);
        $logo1->setOffsetY(1);
        $logo1->setWorksheet($summarySheet);

        // Calculate summary data
        $startStr = request('start');
        $endStr   = request('end');
        $startDate = null;
        $endDate   = null;

        if ($startStr && $endStr) {
            try {
                $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
                $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            } catch (\Throwable $e) {}
        }

        if (empty($startDate) || empty($endDate)) {
            $schoolYear = \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'date_borrowed');
            if ($schoolYear && preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
                list($syStart, $syEnd) = explode('-', $schoolYear);
                $startDate = Carbon::create((int)$syStart, 6, 1)->startOfDay();
                $endDate = Carbon::create((int)$syEnd, 3, 31)->endOfDay();
            } else {
                $now = Carbon::now();
                if ($now->month >= 6) {
                    $startDate = Carbon::create($now->year, 6, 1)->startOfDay();
                    $endDate = Carbon::create($now->year + 1, 3, 31)->endOfDay();
                } else {
                    $startDate = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                    $endDate = Carbon::create($now->year, 3, 31)->endOfDay();
                }
            }
        }

        // Generate list of months
        $monthsList = [];
        $current = $startDate->copy()->startOfMonth();
        $endMonth = $endDate->copy()->startOfMonth();

        $maxMonths = 60;
        $countMonths = 0;
        while ($current->lte($endMonth) && $countMonths < $maxMonths) {
            $monthsList[] = [
                'label' => $current->format('F Y'),
                'month' => $current->month,
                'year' => $current->year,
                'student_sections' => [],
                'student_total' => 0,
                'employee_total' => 0,
            ];
            $current->addMonth();
            $countMonths++;
        }

        // Group and count from data
        foreach ($data as $item) {
            if ($item->date_borrowed) {
                $borrowedDate = Carbon::parse($item->date_borrowed);
                $mNum = $borrowedDate->month;
                $yNum = $borrowedDate->year;

                foreach ($monthsList as &$m) {
                    if ($m['month'] == $mNum && $m['year'] == $yNum) {
                        if ($item->user && $item->user->students) {
                            $secName = ($item->user->students->level ?? 'N/A') . ' - ' . ($item->user->students->section ?? 'N/A');
                            if (!isset($m['student_sections'][$secName])) {
                                $m['student_sections'][$secName] = 0;
                            }
                            $m['student_sections'][$secName]++;
                            $m['student_total']++;
                        } elseif ($item->user && $item->user->employees) {
                            $m['employee_total']++;
                        }
                        break;
                    }
                }
            }
        }
        unset($m);

        // Write summary sheet header
        $summaryTitle = \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Summary Report (' . ($userType === 'student' ? 'Students' : 'Employees') . ')', request('start'), request('end'), $data, 'date_borrowed');
        $maxHeaderCol = $userType === 'student' ? 'C' : 'B';
        
        $summarySheet->mergeCells("A6:{$maxHeaderCol}6");
        $summarySheet->setCellValue('A6', $summaryTitle);
        $summarySheet->getStyle("A6:{$maxHeaderCol}6")->getFont()->setBold(true)->setSize(14);
        $summarySheet->getStyle("A6:{$maxHeaderCol}6")->getAlignment()->setHorizontal('center')->setVertical('center');
 
        $summarySheet->mergeCells("A7:{$maxHeaderCol}7");
        $summarySheet->setCellValue('A7', 'as of ' . date('F j, Y'));
        $summarySheet->getStyle("A7:{$maxHeaderCol}7")->getFont()->setBold(true)->setSize(10);
        $summarySheet->getStyle("A7:{$maxHeaderCol}7")->getAlignment()->setHorizontal('center');

        $summaryRow = 9;
        if ($userType === 'student') {
            // Headers
            $summarySheet->setCellValue('A9', 'Month');
            $summarySheet->setCellValue('B9', 'Grade & Section (Count)');
            $summarySheet->setCellValue('C9', 'Total');

            $summarySheet->getStyle("A9:C9")->getFont()->setBold(true)->setSize(10);
            $summarySheet->getStyle("A9:C9")->getAlignment()->setHorizontal('center');
            $summarySheet->getStyle("A9:C9")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

            $summarySheet->getColumnDimension('A')->setWidth(25);
            $summarySheet->getColumnDimension('B')->setWidth(45);
            $summarySheet->getColumnDimension('C')->setWidth(15);

            $currentRow = 10;
            $studentGrandTotal = 0;
            foreach ($monthsList as $m) {
                $sections = $m['student_sections'];
                $countSections = count($sections);
                
                if ($countSections == 0) {
                    $summarySheet->setCellValue('A' . $currentRow, $m['label']);
                    $summarySheet->setCellValue('B' . $currentRow, 'No student borrowings');
                    $summarySheet->setCellValue('C' . $currentRow, 0);
                    $summarySheet->getStyle("A{$currentRow}:C{$currentRow}")->getAlignment()->setHorizontal('center');
                    $currentRow++;
                } else {
                    $startRow = $currentRow;
                    
                    // Write sections
                    $i = 0;
                    foreach ($sections as $secName => $secCount) {
                        $summarySheet->setCellValue('B' . ($startRow + $i), "{$secName}: {$secCount}");
                        $summarySheet->getStyle('B' . ($startRow + $i))->getAlignment()->setHorizontal('center');
                        $i++;
                    }
                    
                    // Write month label in first row of merged range
                    $summarySheet->setCellValue('A' . $startRow, $m['label']);
                    // Write total in first row of merged range
                    $summarySheet->setCellValue('C' . $startRow, $m['student_total']);
                    
                    // Merge Month cells
                    $summarySheet->mergeCells("A{$startRow}:A" . ($startRow + $countSections - 1));
                    // Merge Total cells
                    $summarySheet->mergeCells("C{$startRow}:C" . ($startRow + $countSections - 1));
                    
                    // Align cell contents
                    $summarySheet->getStyle("A{$startRow}:A" . ($startRow + $countSections - 1))->getAlignment()->setHorizontal('center')->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $summarySheet->getStyle("C{$startRow}:C" . ($startRow + $countSections - 1))->getAlignment()->setHorizontal('center')->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    
                    $studentGrandTotal += $m['student_total'];
                    $currentRow += $countSections;
                }
            }

            // Total row
            $summarySheet->mergeCells("A{$currentRow}:B{$currentRow}");
            $summarySheet->setCellValue('A' . $currentRow, 'Grand Total Borrowed:');
            $summarySheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal('right');
            $summarySheet->setCellValue('C' . $currentRow, $studentGrandTotal);
            $summarySheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal('center');

            $summarySheet->getStyle("A{$currentRow}:C{$currentRow}")->getFont()->setBold(true)->setSize(10);
            $summarySheet->getStyle("A{$currentRow}:C{$currentRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $summarySheet->getStyle("A9:C{$currentRow}")->applyFromArray($styleArray);

            $currentRow += 2;
            $summarySheet->setCellValue('A' . $currentRow, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
            $summarySheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(10);

        } else {
            // Employee summary
            $summarySheet->setCellValue('A9', 'Month');
            $summarySheet->setCellValue('B9', 'Total Books Borrowed');

            $summarySheet->getStyle("A9:B9")->getFont()->setBold(true)->setSize(10);
            $summarySheet->getStyle("A9:B9")->getAlignment()->setHorizontal('center');
            $summarySheet->getStyle("A9:B9")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

            $summarySheet->getColumnDimension('A')->setWidth(60);
            $summarySheet->getColumnDimension('B')->setWidth(60);

            $currentRow = 10;
            $employeeGrandTotal = 0;
            foreach ($monthsList as $m) {
                $summarySheet->setCellValue('A' . $currentRow, $m['label']);
                $summarySheet->setCellValue('B' . $currentRow, $m['employee_total']);
                $summarySheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getAlignment()->setHorizontal('center');
                $employeeGrandTotal += $m['employee_total'];
                $currentRow++;
            }

            // Total row
            $summarySheet->setCellValue('A' . $currentRow, 'Total');
            $summarySheet->setCellValue('B' . $currentRow, $employeeGrandTotal);
            $summarySheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal('center');
            $summarySheet->getStyle("A{$currentRow}:B{$currentRow}")->getFont()->setBold(true)->setSize(10);
            $summarySheet->getStyle("A{$currentRow}:B{$currentRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $summarySheet->getStyle("A9:B{$currentRow}")->applyFromArray($styleArray);

            $currentRow += 2;
            $summarySheet->setCellValue('A' . $currentRow, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
            $summarySheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(10);
        }

        // ----------------------------------------------------
        // SHEET 2: DETAILED RECORDS
        // ----------------------------------------------------
        $detailedSheet = $spreadsheet->createSheet();
        $detailedSheet->setTitle('Detailed Records');
        $detailedSheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $detailedSheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $detailedSheet->getPageSetup()->setFitToWidth(1);
        $detailedSheet->getPageSetup()->setFitToHeight(0);

        // Add logo to Detailed Sheet
        $logo2 = new Drawing();
        $logo2->setName(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo2->setDescription(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo2->setPath($tempLogoPath ?? public_path('img/BPSLogoFull.png'));
        $logo2->setHeight(100);
        $logo2->setCoordinates('D1');
        $logo2->setOffsetX(10);
        $logo2->setOffsetY(1);
        $logo2->setWorksheet($detailedSheet);

        // Determine column width / shift for detailed sheet
        // Column A: Accession, B: Title, C: Name, D: Grade & Section / Position (dynamic)
        // Shifting other columns onwards by 1: E, F, G, H, I, J, K depending on transaction type
        $endCol = 'K';
        if ($type && $type == 'Borrowed') {
            $endCol = 'I';
        } else if ($type && $type == 'Reserved') {
            $endCol = 'H';
        }

        $detailedSheet->mergeCells('A6:' . $endCol . '6');
        $detailedSheet->setCellValue('A6', \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Report (Detailed Records)', request('start'), request('end'), $data, 'date_borrowed'));
        $detailedSheet->getStyle('A6:' . $endCol . '6')->getFont()->setBold(true)->setSize(14);
        $detailedSheet->getStyle('A6:' . $endCol . '6')->getAlignment()->setHorizontal('center')->setVertical('center');

        $detailedSheet->setCellValue('A7', 'as of ' . date('F j, Y'));
        $detailedSheet->getStyle('A7')->getFont()->setBold(true)->setSize(10);

        $detailedSheet->setCellValue('A9', 'Accession');
        $detailedSheet->setCellValue('B9', 'Title');
        $detailedSheet->setCellValue('C9', 'Name');
        
        $roleHeader = ($userType === 'student') ? 'Grade & Section' : 'Position';
        $detailedSheet->setCellValue('D9', $roleHeader);

        $detailedSheet->getColumnDimension('A')->setWidth(20);
        $detailedSheet->getColumnDimension('B')->setWidth(50);
        $detailedSheet->getColumnDimension('C')->setWidth(30);
        $detailedSheet->getColumnDimension('D')->setWidth(25);
        $detailedSheet->getColumnDimension('E')->setWidth(20);
        $detailedSheet->getColumnDimension('F')->setWidth(20);
        $detailedSheet->getColumnDimension('G')->setWidth(20);

        $cells1 = 'A7:' . $endCol . '7';
        $cells2 = 'A9:' . $endCol . '9';

        if ($type && $type == 'Borrowed') {
            $detailedSheet->getColumnDimension('H')->setWidth(20);
            $detailedSheet->getColumnDimension('I')->setWidth(20);
            $detailedSheet->mergeCells('A7:I7');
            $detailedSheet->setCellValue('E9', 'Borrowed');
            $detailedSheet->setCellValue('F9', 'Due');
            $detailedSheet->setCellValue('G9', 'Returned');
            $detailedSheet->setCellValue('H9', 'Transaction Type');
            $detailedSheet->setCellValue('I9', 'Status');
        } else if ($type && $type == 'Reserved') {
            $detailedSheet->getColumnDimension('H')->setWidth(20);
            $detailedSheet->mergeCells('A7:H7');
            $detailedSheet->setCellValue('E9', 'Reserved');
            $detailedSheet->setCellValue('F9', 'Pickup Deadline');
            $detailedSheet->setCellValue('G9', 'Transaction Type');
            $detailedSheet->setCellValue('H9', 'Status');
        } else {
            $detailedSheet->getColumnDimension('H')->setWidth(20);
            $detailedSheet->getColumnDimension('I')->setWidth(20);
            $detailedSheet->getColumnDimension('J')->setWidth(20);
            $detailedSheet->getColumnDimension('K')->setWidth(20);
            $detailedSheet->mergeCells('A7:K7');
            $detailedSheet->setCellValue('E9', 'Reserved');
            $detailedSheet->setCellValue('F9', 'Pickup Deadline');
            $detailedSheet->setCellValue('G9', 'Borrowed');
            $detailedSheet->setCellValue('H9', 'Due');
            $detailedSheet->setCellValue('I9', 'Returned');
            $detailedSheet->setCellValue('J9', 'Transaction Type');
            $detailedSheet->setCellValue('K9', 'Status');
        }

        $detailedSheet->getStyle($cells1)->getFont()->setBold(true)->setSize(10);
        $detailedSheet->getStyle($cells1)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        $detailedSheet->getStyle($cells2)->getFont()->setSize(10)->setBold(true);
        $detailedSheet->getStyle($cells2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $detailedSheet->getStyle($cells2)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $row = 10;
        foreach ($data as $item) {
            if (!$item->book || !$item->user) {
                continue; // Skip if book or user relationship is not loaded
            }
            $detailedSheet->setCellValue('A' . $row, $item->book->accession);
            $detailedSheet->setCellValue('B' . $row, $item->book->title);
            $detailedSheet->setCellValue('C' . $row, $item->user->first_name . ' ' . $item->user->last_name);
            
            // Set User Type specific column value (Grade & Section / Position)
            if ($item->user->students) {
                $detailedSheet->setCellValue('D' . $row, $item->user->students->level . ' - ' . $item->user->students->section);
            } else if ($item->user->employees) {
                $detailedSheet->setCellValue('D' . $row, $item->user->employees->employee_role);
            } else {
                $detailedSheet->setCellValue('D' . $row, 'N/A');
            }

            if ($type && $type == 'All') {
                $detailedSheet->setCellValue('E' . $row, $item->reserved_date ? Carbon::parse($item->reserved_date)->format('M j, Y') : 'Not Reserved');
                $detailedSheet->setCellValue('F' . $row, $item->pickup_deadline ? Carbon::parse($item->pickup_deadline)->format('M j, Y') : 'Not Set');
                $detailedSheet->setCellValue('G' . $row, $item->date_borrowed ? Carbon::parse($item->date_borrowed)->format('M j, Y') : 'Not Borrowed');
                $detailedSheet->setCellValue('H' . $row, $item->due_date ? Carbon::parse($item->due_date)->format('M j, Y') : 'Not Set');
                $detailedSheet->setCellValue('I' . $row, $item->return_date ? Carbon::parse($item->return_date)->format('M j, Y') : 'Not Returned');
                $detailedSheet->setCellValue('J' . $row, $item->transaction_type);
                $detailedSheet->setCellValue('K' . $row, $item->status);
            } else if ($type && $type == 'Borrowed') {
                $detailedSheet->setCellValue('E' . $row, $item->date_borrowed ? Carbon::parse($item->date_borrowed)->format('M j, Y') : 'Not Borrowed');
                $detailedSheet->setCellValue('F' . $row, $item->due_date ? Carbon::parse($item->due_date)->format('M j, Y') : 'Not Set');
                $detailedSheet->setCellValue('G' . $row, $item->return_date ? Carbon::parse($item->return_date)->format('M j, Y') : 'Not Returned');
                $detailedSheet->setCellValue('H' . $row, $item->transaction_type);
                $detailedSheet->setCellValue('I' . $row, $item->status);
            } else if ($type && $type == 'Reserved') {
                $detailedSheet->setCellValue('E' . $row, $item->reserved_date ? Carbon::parse($item->reserved_date)->format('M j, Y') : 'Not Reserved');
                $detailedSheet->setCellValue('F' . $row, $item->pickup_deadline ? Carbon::parse($item->pickup_deadline)->format('M j, Y') : 'Not Set');
                $detailedSheet->setCellValue('G' . $row, $item->transaction_type);
                $detailedSheet->setCellValue('H' . $row, $item->status);
            }
            $detailedSheet->getStyle('A' . $row . ':' . $endCol . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $detailedSheet->getStyle('A' . $row . ':' . $endCol . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $detailedSheet->getStyle('A' . $row . ':' . $endCol . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $detailedSheet->getStyle('A10:' . $endCol . ($row - 1))->applyFromArray($styleArray);

        $row += 2;
        $detailedSheet->mergeCells('A' . $row . ':' . $endCol . $row);
        $detailedSheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':' . $endCol . $row;
        $detailedSheet->getStyle($styleRange)->getFont()->setBold(true)->setSize(10);
        $detailedSheet->getStyle($styleRange)->getAlignment()->setHorizontal('left')->setVertical('left')->setWrapText(true);

        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'transaction-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }
    /**
     * Generates data for the transaction report.
     *
     * @param Request $request
     * @param Transaction $model
     * @param bool $isExport
     * @return Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    private function generateData(Request $request, Transaction $model, bool $isExport = false)
    {
        $startStr = $request->input('start');
        $endStr   = $request->input('end');
        $search   = strtolower($request->input('search'));
        $type     = $request->input('type', 'All');
        $subjectId = $request->input('subject_id', 'All');
        $userType = $request->input('user_type', 'student');
        $perPage  = $request->input('perPage', 10);

        $query = $model->newQuery()
            ->with([
                'book:id,title,accession',
                'user:id,first_name,middle_name,last_name,privilege_id',
                'user.students',
                'user.employees',
                'user.privileges'
            ])
            ->select('tr_transactions.*')
            ->whereHas('book')
            ->whereHas('user');

        if ($userType === 'student') {
            $query->whereHas('user.students');
        } elseif ($userType === 'employee') {
            $query->whereHas('user.employees');
        }

        if (strlen($search) > 0) {
            $query->where(function ($group) use ($search) {

                $group->whereHas('user', function ($q) use ($search) {
                    $searchTerms = array_filter(explode(' ', $search));
                    $q->where(function ($queryWrapper) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $queryWrapper->where(function ($sub) use ($term) {
                                $sub->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                            });
                        }
                    });
                });

                $group->orWhereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('accession', 'like', "%{$search}%");
                });

                $group->orWhere('transaction_type', 'like', "%{$search}%");
            });
        }

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('date_borrowed', [$startDate, $endDate]);
        } else {
            // Default to Philippine school year (June to March next year)
            $now = Carbon::now();
            if ($now->month >= 6) {
                $startDate = Carbon::create($now->year, 6, 1)->startOfDay();
                $endDate = Carbon::create($now->year + 1, 3, 31)->endOfDay();
            } else {
                $startDate = Carbon::create($now->year - 1, 6, 1)->startOfDay();
                $endDate = Carbon::create($now->year, 3, 31)->endOfDay();
            }
            $query->whereBetween('date_borrowed', [$startDate, $endDate]);
        }

        if ($type && $type !== 'All') {
            $query->where('transaction_type', $type);
        }

        if ($subjectId && $subjectId !== 'All') {
            $query->whereHas('book.subjectAccessCodes', function($q) use ($subjectId) {
                $q->where('bk_subject_access_codes.id', $subjectId);
            });
        }

        $query->join('bk_books as books', 'tr_transactions.book_id', '=', 'books.id')
            ->join('bk_categories as categories', 'books.category_id', '=', 'categories.id')
            ->orderBy('categories.legend', 'asc')
            ->orderBy('categories.name', 'asc')
            ->orderBy('books.title', 'asc');
        if ($isExport) {
            $data = $query->get();
            $data->reporting_period = \App\Helpers\ReportHelper::buildReportingPeriod($data, 'date_borrowed');
            $data->makeHidden(['id', 'book_id', 'user_id']);
            return $data;
        }

        $result = $query->paginate($perPage)->appends($request->all());
        $result->getCollection()->transform(function ($item) {
            return $item->makeHidden(['id', 'book_id', 'user_id']);
        });

        return $result;
    }
    /**
     * Extracts enum values from a database table column.
     *
     * @param string $table The name of the database table.
     * @param string $columnName The name of the column.
     * @return array An array of enum values.
     */
    private function extract_enums($table, $columnName)
    {
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];

        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        $enumValues = array_merge(['All'], $enumValues);
        return $enumValues;
    }
}
