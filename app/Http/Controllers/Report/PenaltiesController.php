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
use Illuminate\Support\Facades\DB;
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

        $data           = $this->generateData($request);
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
            'end'           => 'nullable|date',
            'search'        => 'nullable',
            'perPage'       => 'nullable|numeric|in:10,25,50'
        ]);
        if ($validator->fails()) {
            Log::warning('Penalties Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            Log::info('Penalties Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, true);
            $this->generatePDF($data);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Penalties Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, true);
            $this->exportExcel($data);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, false);
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
     * @param bool $isExport
     * @return Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    private function generateData(Request $request, bool $isExport = false)
    {
        $fromInputDate = $request->input('start');
        $toInputDate   = $request->input('end');
        $search        = strtolower($request->input('search'));
        $perPage       = $request->input('perPage', 10);

        // Eager load penalties and their rule
        $query = Transaction::with(['user', 'book', 'penalties.penaltyRule'])
            ->whereHas('penalties')
            ->whereHas('user')
            ->whereHas('book')
            ->whereHas('penalties.penaltyRule')
            ->where('penalty_total', '>', 0)
            ->orderBy('updated_at', 'desc');

        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $start = Carbon::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $end   = Carbon::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
        }

        if (!empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(DB::raw('LOWER(first_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(middle_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(CONCAT(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(CONCAT(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(CONCAT(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(CONCAT(last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('LOWER(CONCAT(first_name, " ", last_name))'), 'like', '%' . $search . '%');
            });
        }

        if ($isExport) {
            $transactions = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            $transactions = $transactions->map(function ($transaction) {
                $violations = $transaction->penalties
                    ->pluck('penaltyRule.type')   // <- correct field name: type
                    ->filter()                   // drop null/empty
                    ->unique()
                    ->values()
                    ->implode(', ');

                $transaction->violation = $violations ?: '-';
                return $transaction;
            });

            $minDate = $transactions->min(fn($item) => Carbon::parse($item->created_at));
            $maxDate = $transactions->max(fn($item) => Carbon::parse($item->created_at));

            $transactions->reporting_period = $minDate && $maxDate
                ? $minDate->format('F j, Y') . ' to ' . $maxDate->format('F j, Y')
                : 'N/A';

            return $transactions;
        } else {
            $paginated = $query->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->paginate($perPage)
                ->appends([
                    'start' => $fromInputDate,
                    'end' => $toInputDate,
                    'search' => $search,
                    'perPage' => $perPage
                ]);

            // compute violation on each item in the current page
            $paginated->getCollection()->transform(function ($transaction) {
                $violations = $transaction->penalties
                    ->pluck('penaltyRule.type')
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(', ');

                $transaction->violation = $violations ?: '-';
                return $transaction;
            });

            return $paginated;
        }
    }
}
