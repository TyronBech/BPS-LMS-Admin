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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::error('Penalties Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Transaction(), false);
        return view('report.penalties.index', compact('data', 'fromInputDate', 'toInputDate', 'search', 'perPage'));
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

        Log::info('Penalties Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['search', 'start', 'end', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::error('Penalties Report: Validation failed', [
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
            $data = $this->generateData($request, new Transaction(), true);
            $this->generatePDF($data);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Penalties Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Transaction(), true);
            $this->exportExcel($data);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new Transaction(), false);
        return view('report.penalties.index', compact('data', 'search', 'fromInputDate', 'toInputDate', 'perPage'));
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
    private function generatePDF(Collection $data)
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
            'date'          => date('F j, Y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.penalties-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
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
        $logo->setHeight(80);
        $logo->setCoordinates('C1');
        $logo->setOffsetX(90);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Overdue Fines Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->mergeCells('A7:I7');
        $sheet->mergeCells('A8:I8');
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:I8')->getFont()->setBold(true);
        $sheet->getStyle('A7:I8')->getFont()->setSize(10);
        $sheet->getStyle('A7:I8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:I8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:I8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:I10')->getFont()->setSize(12);
        $sheet->getStyle('A10:I10')->getFont()->setBold(true);
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
            $sheet->setCellValue('D' . $row, $item->date_borrowed);
            $sheet->setCellValue('E' . $row, $item->due_date ?? 'Not Returned');
            $sheet->setCellValue('F' . $row, $item->return_date ?? 'Not Returned');
            $sheet->setCellValue('G' . $row, $item->violation);
            $sheet->setCellValue('H' . $row, number_format($item->penalty_total, 2));
            $sheet->setCellValue('I' . $row, $item->penalty_status);

            $row++;
        }
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
        $startStr = $request->input('start');
        $endStr   = $request->input('end');
        $search   = strtolower($request->input('search'));
        $perPage  = $request->input('perPage', 10);

        $query = $model->newQuery()
            ->with([
                'user:id,first_name,middle_name,last_name',
                'book:id,title,accession',
                'penalties.penaltyRule'
            ])
            ->select([
                'id',
                'user_id',
                'book_id',
                'date_borrowed as borrowed',
                'due_date as due',
                'return_date as returned',
                'penalty_total as total',
                'penalty_status as status',
                'remarks'
            ])
            ->whereHas('penalties')
            ->whereHas('user')
            ->whereHas('book')
            ->whereHas('penalties.penaltyRule')
            ->where('penalty_total', '>', 0);

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        if ($isExport) {
            $data = $query->get();

            if ($data->isNotEmpty()) {
                $max = $data->first()->date;
                $min = $data->last()->date;
                $data->reporting_period = Carbon::parse($min)->format('F j, Y') . ' to ' . Carbon::parse($max)->format('F j, Y');
            } else {
                $data->reporting_period = 'N/A';
            }

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

    /**
     * Helper to process the penalty string and clean up the object.
     * This prevents code duplication between Export and Pagination logic.
     * 
     * @param Transaction $transaction
     * @return Transaction
     */
    private function formatPenaltyRow(Transaction $transaction)
    {
        // Extract violation types (e.g., "Overdue, Damaged")
        $violations = $transaction->penalties
            ->pluck('penaltyRule.type')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        // Append the new custom attribute
        $transaction->violation = $violations ?: '-';

        return $transaction;
    }
}
