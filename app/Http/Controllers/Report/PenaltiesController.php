<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\UISetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PenaltiesController extends Controller
{
    /**
     * Handles the page request for the penalties report.
     * It takes in the request object and extracts the search term, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, search term, start date, end date, page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $search         = $request->input('search');
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $perPage        = $request->input('perPage', 10);
        $penaltyStatus  = $request->input('penalty_status');
        $penaltyStatuses = $this->getPenaltyStatuses();

        Log::info('Penalties Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'penalty_status' => 'nullable|in:' . implode(',', $penaltyStatuses),
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Penalties Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Transaction(), false);

        // Build a fresh full collection (unpaginated) from the same filters so summary reflects all rows
        try {
            $fullQuery = $this->buildPenaltyQuery($request, new Transaction());
            $fullQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc');
            $full = $fullQuery->get();
            $full->transform(function ($item) {
                return $this->formatPenaltyRow($item);
            });
            $full->each(function ($item) {
                $item->makeHidden(['id', 'user_id', 'book_id', 'penalties']);
            });
        } catch (\Throwable $e) {
            Log::warning('Penalties Report: failed to build full collection for summary in index', ['error' => $e->getMessage()]);
            $full = ($data instanceof LengthAwarePaginator) ? $data->getCollection() : $data;
        }

        $summary = $this->calculateSummary($full);
        return view('report.penalties.index', compact('data', 'summary', 'fromInputDate', 'toInputDate', 'search', 'perPage', 'penaltyStatus', 'penaltyStatuses'));
    }
    /**
     * Handles the search request for the penalties report.
     * It takes in the request object and extracts the search term, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * If the validation fails, it logs a warning message with the user id, errors, ip address and timestamp.
     * If the submit button is 'pdf', it generates the PDF export.
     * If the submit button is 'excel', it generates the Excel export.
     * Finally, it generates the data for the report and returns the view with the data, search term, start date, end date, page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $search         = $request->input('search');
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $perPage        = $request->input('perPage', 10);
        $penaltyStatus  = $request->input('penalty_status');
        $penaltyStatuses = $this->getPenaltyStatuses();

        Log::info('Penalties Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['search', 'start', 'end', 'penalty_status', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'penalty_status' => 'nullable|in:' . implode(',', $penaltyStatuses),
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Penalties Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->input('submit') == 'pdf') {
            Log::info('Penalties Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data    = $this->generateData($request, new Transaction(), true);
            $reportingPeriod = $this->buildReportingPeriodLabel($data);
            $summary = $this->generateSummaryFromDataset($data);
            $this->generatePDF($data, $summary, $reportingPeriod);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Penalties Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data    = $this->generateData($request, new Transaction(), true);
            $reportingPeriod = $this->buildReportingPeriodLabel($data);
            $summary = $this->generateSummaryFromDataset($data);
            $this->exportExcel($data, $summary, $reportingPeriod);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new Transaction(), false);

        // Build a fresh full collection (unpaginated) from the same filters so summary reflects all rows
        try {
            $fullQuery = $this->buildPenaltyQuery($request, new Transaction());
            $fullQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc');
            $full = $fullQuery->get();
            $full->transform(function ($item) {
                return $this->formatPenaltyRow($item);
            });
            $full->each(function ($item) {
                $item->makeHidden(['id', 'user_id', 'book_id', 'penalties']);
            });
        } catch (\Throwable $e) {
            Log::warning('Penalties Report: failed to build full collection for summary in search', ['error' => $e->getMessage()]);
            $full = ($data instanceof LengthAwarePaginator) ? $data->getCollection() : $data;
        }

        $summary = $this->calculateSummary($full);
        return view('report.penalties.index', compact('data', 'summary', 'search', 'fromInputDate', 'toInputDate', 'perPage', 'penaltyStatus', 'penaltyStatuses'));
    }
    /**
     * Generates a PDF report for the overdue fines report.
     *
     * It takes in the data to be included in the report and generates a PDF report.
     * The report includes the title, school name, school address, logo, user name, date, data and total count.
     * The PDF report is then streamed to the browser with the filename 'overdue-fines-report <date>.pdf'.
     *
     * @param \Illuminate\Database\Eloquent\Collection $data The data to be included in the report.
     */
    private function generatePDF(Collection $data, array $summary, ?string $reportingPeriod = null)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => 'Overdue Fines Report',
            'school'        => $settings->org_name ?? "Bicutan Parochial School, Inc.",
            'address'       => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'logo'          => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
            'user'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date'          => "as of " . date('F j, Y'),
            'data'          => $data,
            'summary'       => $summary,
            'reporting_period' => $reportingPeriod,
            'totalCount'    => $data->count(),
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.penalties-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('overdue-fines-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the penalties report to an Excel file.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $data  The data to be included in the report.
     *
     * @return void
     */
    private function exportExcel(Collection $data, array $summary, ?string $reportingPeriod = null)
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
        $logo->setHeight(80);
        $logo->setCoordinates('C1');
        $logo->setOffsetX(90);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Overdue Fines Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->mergeCells('A6:I6');
        $sheet->setCellValue('A6', 'Overdue Fines Report');
        $sheet->getStyle('A6:I6')->getFont()->setBold(true);
        $sheet->getStyle('A6:I6')->getFont()->setSize(14);
        $sheet->getStyle('A6:I6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:I6')->getAlignment()->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->mergeCells('A8:I8');
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:I8')->getFont()->setBold(true);
        $sheet->getStyle('A7:I8')->getFont()->setSize(10);
        $sheet->getStyle('A7:I8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:I8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:I8')->getAlignment()->setWrapText(true);

        // Reporting period (if provided)
        $sheet->mergeCells('A9:I9');
        $sheet->setCellValue('A9', $reportingPeriod ?? '');
        $sheet->getStyle('A9:I9')->getFont()->setBold(false);
        $sheet->getStyle('A9:I9')->getFont()->setSize(10);
        $sheet->getStyle('A9:I9')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A9:I9')->getAlignment()->setVertical('left');
        $sheet->getStyle('A9:I9')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:I10')->getFont()->setSize(10);
        $sheet->getStyle('A10:I10')->getFont()->setBold(true);
        $sheet->getStyle('A10:I10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10:I10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $sheet->setCellValue('A10', 'Name');
        $sheet->setCellValue('B10', 'Accession');
        $sheet->setCellValue('C10', 'Book');
        $sheet->setCellValue('D10', 'Borrowed Date');
        $sheet->setCellValue('E10', 'Due Date');
        $sheet->setCellValue('F10', 'Returned Date');
        $sheet->setCellValue('G10', 'Violation');
        $sheet->setCellValue('H10', 'Amount');
        $sheet->setCellValue('I10', 'Status');
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->user->first_name . ' ' . $item->user->last_name);
            $sheet->setCellValue('B' . $row, $item->book->accession);
            $sheet->setCellValue('C' . $row, $item->book->title);
            $sheet->setCellValue('D' . $row, $item->borrowed);
            $sheet->setCellValue('E' . $row, $item->due);
            $sheet->setCellValue('F' . $row, $item->returned);
            $sheet->setCellValue('G' . $row, $item->violation);
            $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            if ($item->has_discount) {
                $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                $originalAmount = $richText->createTextRun('₱ ' . number_format($item->actual_total, 2));
                $originalAmount->getFont()->setStrikethrough(true);
                $richText->createText("\n");
                $discountedAmount = $richText->createTextRun('₱ ' . number_format($item->total, 2));
                $discountedAmount->getFont()->setBold(true);
                $discountedAmount->getFont()->getColor()->setARGB('FF16A34A');
                $discountLabel = $richText->createTextRun('  ' . $item->discount_percent_label . ' discount');
                $discountLabel->getFont()->getColor()->setARGB('FF16A34A');
                $sheet->getCell('H' . $row)->setValue($richText);
                $sheet->getRowDimension($row)->setRowHeight(32);
            } else {
                $sheet->setCellValue('H' . $row, '₱ ' . number_format($item->total, 2));
            }
            $sheet->setCellValue('I' . $row, $item->status);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setWrapText(true);

            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:I' . ($row - 1))->applyFromArray($styleArray);

        $row += 2;

        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'Payment Summary');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF');
        $row++;

        foreach ($summary['rows'] as $summaryRow) {
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->setCellValue('A' . $row, $summaryRow['label']);
            // write numeric amount and apply currency format
            $sheet->setCellValue('D' . $row, (float) ($summaryRow['amount'] ?? 0));
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('"₱"#,##0.00');

            $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            if ($summaryRow['is_total']) {
                $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9FAFB');
            }

            $row++;
        }

        $row++;
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':I' . $row;
        $sheet->getStyle($styleRange)->getFont()->setBold(true);
        $sheet->getStyle($styleRange)->getFont()->setSize(10);
        $sheet->getStyle($styleRange)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($styleRange)->getAlignment()->setVertical('left');
        $sheet->getStyle($styleRange)->getAlignment()->setWrapText(true);

        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'penalties-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }

    private function buildReportingPeriodLabel(Collection $data): string
    {
        $dueDates = $data->map(function ($item) {
            $rawDueDate = $item->due_date ?? null;

            if (!$rawDueDate) {
                return null;
            }

            try {
                return Carbon::parse($rawDueDate);
            } catch (\Throwable $e) {
                return null;
            }
        })->filter();

        if ($dueDates->isNotEmpty()) {
            $earliest = $dueDates->sortBy(fn($date) => $date->timestamp)->first()->format('F j, Y');
            $latest = $dueDates->sortByDesc(fn($date) => $date->timestamp)->first()->format('F j, Y');
            return 'Reporting Period: ' . $earliest . ' to ' . $latest;
        }

        return 'Reporting Period: Due Date Not Available';
    }
    /**
     * Generates data for the penalties report.
     *
     * @param Request $request
     * @param Transaction $model
     * @param bool $isExport
     * @return Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    private function generateData(Request $request, Transaction $model, bool $isExport = false)
    {
        $perPage  = $request->input('perPage', 10);
        $query = $this->buildPenaltyQuery($request, $model);

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        if ($isExport) {
            $data = $query->get();
            $data->transform(function ($item) {
                return $this->formatPenaltyRow($item);
            });
            $data->makeHidden(['id', 'user_id', 'book_id', 'penalties']);
            return $data;
        }

        $result = $query->paginate($perPage)->appends($request->all());
        $result->getCollection()->transform(function ($item) {
            return $this->formatPenaltyRow($item);
        });
        $result->getCollection()->each(function ($item) {
            $item->makeHidden(['id', 'user_id', 'book_id', 'penalties']);
        });

        return $result;
    }

    private function generateSummaryFromDataset($data): array
    {
        if ($data instanceof LengthAwarePaginator) {
            $items = $data->getCollection();
        } else {
            $items = $data;
        }

        if (!($items instanceof Collection)) {
            $items = collect($items);
        }

        return $this->calculateSummary($items instanceof Collection ? $items : collect($items));
    }

    private function buildPenaltyQuery(Request $request, Transaction $model)
    {
        $startStr = $request->input('start');
        $endStr   = $request->input('end');
        $search   = strtolower((string) $request->input('search'));
        $penaltyStatus = $this->normalizePenaltyStatus($request->input('penalty_status'));

        $query = $this->buildPenaltyBaseQuery($model);

        if ($startStr && $endStr) {
            $startDate = $this->parseDateInput($startStr)?->startOfDay();
            $endDate   = $this->parseDateInput($endStr)?->endOfDay();

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(last_name, ", ", first_name)) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($penaltyStatus) {
            $query->where('penalty_status', $penaltyStatus);
        }

        return $query;
    }

    private function buildPenaltyBaseQuery(Transaction $model)
    {
        return $model->newQuery()
            ->with([
                'user:id,first_name,middle_name,last_name',
                'book:id,title,accession',
                'penalties.penaltyRule'
            ])
            ->withSum('penalties as penalties_amount_sum', 'amount')
            ->whereHas('user')
            ->whereHas('book')
            ->where(function ($subQuery) {
                $subQuery->where('penalty_total', '>', 0)
                    ->orWhereHas('penalties', function ($penaltyQuery) {
                        $penaltyQuery->where('amount', '>', 0);
                    });
            });
    }

    private function normalizePenaltyStatus($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $status = trim((string) $value);
        if ($status === '' || strcasecmp($status, 'All') === 0) {
            return null;
        }

        return $status;
    }

    private function getPenaltyStatuses(): array
    {
        return $this->extract_enums((new Transaction())->getTableName(), 'penalty_status');
    }

    /**
     * Helper to process the penalty string and clean up the object.
     * This prevents code duplication between Export and Pagination logic.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    private function formatPenaltyRow(Transaction $transaction)
    {
        $violations = $transaction->penalties
            ->pluck('penaltyRule.type')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        $resolvedTotal = (float) ($transaction->penalties_amount_sum ?? 0);
        if ($resolvedTotal <= 0) {
            $resolvedTotal = (float) ($transaction->penalty_total ?? 0);
        }
        $discountRate = $this->normalizeDiscountRate($transaction->discount ?? 0);
        $discountedTotal = $discountRate > 0
            ? round($resolvedTotal * (1 - $discountRate), 2)
            : $resolvedTotal;
        $hasDiscount = $discountRate > 0 && $discountedTotal < $resolvedTotal;
        $discountAmount = max(round($resolvedTotal - $discountedTotal, 2), 0);

        $transaction->borrowed = $transaction->date_borrowed
            ? Carbon::parse($transaction->date_borrowed)->format('M j, Y')
            : 'Not Borrowed';
        $transaction->due = $transaction->due_date
            ? Carbon::parse($transaction->due_date)->format('M j, Y')
            : 'No Due Date';
        $transaction->returned = $transaction->return_date
            ? Carbon::parse($transaction->return_date)->format('M j, Y')
            : 'Unreturned';
        $transaction->actual_total = $resolvedTotal;
        $transaction->discount_rate = $discountRate;
        $transaction->discount_percent_label = $this->formatDiscountPercent($discountRate);
        $transaction->discount_amount = $discountAmount;
        $transaction->has_discount = $hasDiscount;
        $transaction->discounted_total = $discountedTotal;
        $transaction->total = $discountedTotal;
        $transaction->status = $transaction->penalty_status ?: 'No Penalty';
        $transaction->violation = $violations ?: '-';

        return $transaction;
    }

    private function calculateSummary(Collection $items): array
    {
        $summary = [
            'penalty_amount' => 0.0,
            'discounted_amount' => 0.0,
            'waived_amount' => 0.0,
            'unpaid_amount' => 0.0,
            'other_amount' => 0.0,
            'paid_amount' => 0.0,
        ];

        foreach ($items as $item) {
            $status = strtolower(trim((string) $item->status));
            $actualTotal = (float) ($item->actual_total ?? 0);
            $discountAmount = (float) ($item->discount_amount ?? 0);
            $total = (float) ($item->total ?? 0);

            $summary['penalty_amount'] += $actualTotal;
            $summary['discounted_amount'] += $discountAmount;

            // Classification rules:
            // - Paid related: statuses 'paid' and 'discounted' (treated as collected amounts)
            // - Unpaid related: statuses 'unpaid' and 'waived' (not collected) and the discount portion (discounted_amount)
            // - Other: any other statuses
            if ($status === 'waived') {
                // waived means the whole actual total is not collectible
                $summary['waived_amount'] += $actualTotal;
            } elseif ($status === 'paid' || $status === 'discounted') {
                // paid or discounted -> collected amount is the discounted total
                $summary['paid_amount'] += $total;
            } elseif ($status === 'unpaid') {
                // unpaid -> remaining (not collected)
                $summary['unpaid_amount'] += $total;
            } else {
                $summary['other_amount'] += $total;
            }
        }

        foreach ($summary as $key => $value) {
            $summary[$key] = round($value, 2);
        }

        $summary['total_collectible'] = round(max(
            $summary['penalty_amount'] - $summary['discounted_amount'] - $summary['waived_amount'],
            0
        ), 2);
        // Paid collectible is what was collected (paid + discounted totals)
        $summary['paid_collectible'] = $summary['paid_amount'];

        // Unpaid collectible per your rule: waived + unpaid + discount amount
        $summary['unpaid_collectible'] = round($summary['waived_amount'] + $summary['unpaid_amount'] + $summary['discounted_amount'], 2);

        // Keep other_amount separate; non-paid related total may include other amounts if desired
        $summary['non_paid_related_total'] = round($summary['unpaid_collectible'] + $summary['other_amount'], 2);
        $summary['outstanding'] = $summary['unpaid_collectible'];
        $summary['current_balance'] = $summary['outstanding'];
        $summary['current_balance'] = $summary['outstanding'];

        $summary['rows'] = [
            ['label' => 'Penalty Amount', 'amount' => $summary['penalty_amount'], 'is_total' => false],
            ['label' => 'Amount Discounted', 'amount' => $summary['discounted_amount'], 'is_total' => false],
            ['label' => 'Amount Waived', 'amount' => $summary['waived_amount'], 'is_total' => false],
            ['label' => 'Not Paid Amount', 'amount' => $summary['unpaid_amount'], 'is_total' => false],
            ['label' => 'Other Amount', 'amount' => $summary['other_amount'], 'is_total' => false],
            ['label' => 'Total Collectible', 'amount' => $summary['total_collectible'], 'is_total' => true],
            ['label' => 'Paid Collectible', 'amount' => $summary['paid_collectible'], 'is_total' => true],
            ['label' => 'Unpaid Collectible', 'amount' => $summary['unpaid_collectible'], 'is_total' => true],
        ];

        return $summary;
    }

    private function normalizeDiscountRate($value): float
    {
        $discount = (float) $value;

        if ($discount > 1) {
            $discount /= 100;
        }

        return max(0, min($discount, 1));
    }

    private function formatDiscountPercent(float $rate): string
    {
        $percent = $rate * 100;
        $formatted = number_format($percent, 2);
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted . '%';
    }

    private function parseDateInput(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        foreach (['m/d/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
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
