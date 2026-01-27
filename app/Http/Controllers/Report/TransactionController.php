<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\UISetting;
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
        $availability   = $this->extract_enums((new Transaction())->getTable(), 'transaction_type');

        Log::info('Transaction Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'start_date' => $fromInputDate,
                'end_date' => $toInputDate,
                'search' => $search,
                'type' => $type,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'type'          => 'nullable|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::error('Transaction Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->route('report.circulation')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Transaction(), false);
        return view('report.transactions.transactions', compact('data', 'search', 'fromInputDate', 'toInputDate', 'type', 'perPage', 'availability'));
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
        $availability   = $this->extract_enums((new Transaction())->getTable(), 'transaction_type');
        $perPage        = $request->input('perPage', 10);

        Log::info('Transaction Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['start', 'end', 'search', 'type', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'type'          => 'nullable|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::error('Transaction Report: Validation failed', [
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
            $this->generatePDF($data, $type);
            return redirect()->route('report.circulation')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            Log::info('Transaction Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Transaction(), true);
            $this->exportExcel($data, $type);
            return redirect()->route('report.circulation')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, new Transaction(), false);
        return view('report.transactions.transactions', compact('data', 'search', 'fromInputDate', 'toInputDate', 'type', 'perPage', 'availability'));
    }
    /**
     * Generates a PDF report for the transaction report.
     *
     * It takes in the data to be included in the report and the type of report to be generated.
     * The report includes the title, school name, type, logo, address, user name, date, data and total count.
     * The PDF report is then streamed to the browser with the filename 'transaction-report <date>.pdf'.
     *
     * @param Illuminate\Database\Eloquent\Collection $data The data to be included in the report.
     * @param string $type The type of report to be generated.
     */
    private function generatePDF(Collection $data, string $type)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => 'Book Circulation Report',
            'school'        => $settings->org_name ?? "Bicutan Parochial School, Inc.",
            'type'          => $type,
            'logo'          => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
            'address'       => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
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
        $dompdf->loadHtml(view('pdf.transaction-pdf-report', $items));
        $dompdf->setPaper('legal', 'landscape');
        $dompdf->render();
        $dompdf->stream('transaction-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the transaction report to an Excel file.
     * 
     * @param  Illuminate\Database\Eloquent\Collection  $data  The data to be included in the report.
     * @param  string  $type  The type of report to be generated.
     * 
     * @return void
     */
    private function exportExcel(Collection $data, string $type)
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
        $logo->setCoordinates('B1');
        $logo->setOffsetX(300);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Book Circulation Report');
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->setCellValue('A10', 'Accession');
        $sheet->setCellValue('B10', 'Title');
        $sheet->setCellValue('C10', 'Name');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Default values for cells
        $cells1 = 'A7:J8';
        $cells2 = 'A10:J10';

        if ($type && $type == 'Borrowed') {
            $cells1 = 'A7:H8';
            $cells2 = 'A10:H10';
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->mergeCells('A7:H7');
            $sheet->mergeCells('A8:H8');
            $sheet->setCellValue('D10', 'Borrowed');
            $sheet->setCellValue('E10', 'Due');
            $sheet->setCellValue('F10', 'Returned');
            $sheet->setCellValue('G10', 'Transaction Type');
            $sheet->setCellValue('H10', 'Status');
        } else if ($type && $type == 'Reserved') {
            $cells1 = 'A7:G8';
            $cells2 = 'A10:G10';
            $sheet->mergeCells('A7:G7');
            $sheet->mergeCells('A8:G8');
            $sheet->setCellValue('D10', 'Reserved');
            $sheet->setCellValue('E10', 'Pickup Deadline');
            $sheet->setCellValue('F10', 'Transaction Type');
            $sheet->setCellValue('G10', 'Status');
        } else {
            $cells1 = 'A7:J8';
            $cells2 = 'A10:J10';
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->mergeCells('A7:J7');
            $sheet->mergeCells('A8:J8');
            $sheet->setCellValue('D10', 'Reserved');
            $sheet->setCellValue('E10', 'Pickup Deadline');
            $sheet->setCellValue('F10', 'Borrowed');
            $sheet->setCellValue('G10', 'Due');
            $sheet->setCellValue('H10', 'Returned');
            $sheet->setCellValue('I10', 'Transaction Type');
            $sheet->setCellValue('J10', 'Status');
        }

        $sheet->getStyle($cells1)->getFont()->setBold(true);
        $sheet->getStyle($cells1)->getFont()->setSize(10);
        $sheet->getStyle($cells1)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cells1)->getAlignment()->setVertical('left');
        $sheet->getStyle($cells1)->getAlignment()->setWrapText(true);
        $sheet->getStyle($cells2)->getFont()->setSize(12);
        $sheet->getStyle($cells2)->getFont()->setBold(true);
        $row = 11;
        foreach ($data as $item) {
            if (!$item->book || !$item->user) {
                continue; // Skip if book or user relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->book->accession);
            $sheet->setCellValue('B' . $row, $item->book->title);
            $sheet->setCellValue('C' . $row, $item->user->first_name . ' ' . $item->user->last_name);
            if ($type && $type == 'All') {
                $sheet->setCellValue('D' . $row, $item->reserved_date ? $item->reserved_date : 'Not Reserved');
                $sheet->setCellValue('E' . $row, $item->pickup_deadline ? $item->pickup_deadline : 'Not Set');
                $sheet->setCellValue('F' . $row, $item->date_borrowed ? $item->date_borrowed : 'Not Borrowed');
                $sheet->setCellValue('G' . $row, $item->due_date ? $item->due_date : 'Not Set');
                $sheet->setCellValue('H' . $row, $item->return_date ? $item->return_date : 'Not Returned');
                $sheet->setCellValue('I' . $row, $item->transaction_type);
                $sheet->setCellValue('J' . $row, $item->status);
            } else if ($type && $type == 'Borrowed') {
                $sheet->setCellValue('D' . $row, $item->date_borrowed ? $item->date_borrowed : 'Not Borrowed');
                $sheet->setCellValue('E' . $row, $item->due_date ? $item->due_date : 'Not Set');
                $sheet->setCellValue('F' . $row, $item->return_date ? $item->return_date : 'Not Returned');
                $sheet->setCellValue('G' . $row, $item->transaction_type);
                $sheet->setCellValue('H' . $row, $item->status);
            } else if ($type && $type == 'Reserved') {
                $sheet->setCellValue('D' . $row, $item->reserved_date ? $item->reserved_date : 'Not Reserved');
                $sheet->setCellValue('E' . $row, $item->pickup_deadline ? $item->pickup_deadline : 'Not Set');
                $sheet->setCellValue('F' . $row, $item->transaction_type);
                $sheet->setCellValue('G' . $row, $item->status);
            }
            $row++;
        }
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
        $perPage  = $request->input('perPage', 10);

        $query = $model->newQuery()
            ->with([
                'book:id,title,accession',
                'user:id,first_name,middle_name,last_name'
            ])
            ->select([
                'id',
                'book_id',
                'user_id',
                'transaction_type as type',
                'date_borrowed as borrowed',
                'return_date as returned',
                'due_date as due',
                'pickup_deadline as deadline',
                'reserved_date as reserved',
                'status'
            ])
            ->whereHas('book')
            ->whereHas('user')
            ->whereNotNull('date_borrowed');

        if (strlen($search) > 0) {
            $query->where(function ($group) use ($search) {

                $group->whereHas('user', function ($q) use ($search) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(last_name, ", ", first_name)) LIKE ?', ["%{$search}%"]);
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
        }

        if ($type && $type !== 'All') {
            $query->where('transaction_type', $type);
        }

        $query->orderBy('date_borrowed', 'desc')->orderBy('id', 'desc');
        if ($isExport) {
            $data = $query->get();

            if ($data->isNotEmpty()) {
                $max = $data->first()->borrowed;
                $min = $data->last()->borrowed;

                $data->reporting_period = Carbon::parse($min)->format('F j, Y') . ' to ' . Carbon::parse($max)->format('F j, Y');
            } else {
                $data->reporting_period = 'N/A';
            }

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
