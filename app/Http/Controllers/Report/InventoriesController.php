<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\UISetting;
use Illuminate\Support\Facades\DB;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InventoriesController extends Controller
{
    /**
     * Handles the page request for the inventory report.
     *
     * It extracts the start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, start date, end date and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);

        Log::info('Inventory Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'start_date' => $fromInputDate,
                'end_date' => $toInputDate,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'perPage'       => 'nullable|numeric|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Inventory Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Inventory(), false);
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data', 'perPage'));
    }
    /**
     * Handles the search request for the inventory report.
     * It extracts the start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * If the validation fails, it logs a warning message with the user id, errors, ip address and timestamp.
     * If the submit button is 'pdf', it generates the PDF export.
     * If the submit button is 'excel', it generates the Excel export.
     * Finally, it generates the data for the report and returns the view with the data, start date, end date and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $start          = null;
        $end            = null;

        Log::info('Inventory Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['start', 'end', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $data           = Inventory::with('book')->where('checked_at', '!=', null);
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'perPage'       => 'nullable|numeric|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Inventory Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->input('submit') == 'pdf') {
            Log::info('Inventory Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Inventory(), true);
            $this->generatePDF($data);
            return redirect()->back()->with('toast-success', 'PDF generated successfully');
        }
        if ($request->input('submit') == 'excel') {
            Log::info('Inventory Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, new Inventory(), false);
            $this->exportExcel($data);
            return redirect()->back()->with('toast-success', 'Excel generated successfully');
        }
        $data = $this->generateData($request, new Inventory(), false);
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data', 'perPage'));
    }
    /**
     * Generates a PDF report for the inventory report.
     * 
     * @param  \Illuminate\Database\Eloquent\Collection $data The data to be included in the report.
     * 
     * @return void
     */
    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => 'Inventory Report',
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
        $dompdf->loadHtml(view('pdf.inventory-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('inventory-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the inventory report to an Excel file.
     * 
     * @param  \Illuminate\Database\Eloquent\Collection $data The data to be included in the report.
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
        $logo->setOffsetX(10);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Book Circulation Report');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(60);
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
        $sheet->setCellValue('A10', 'Accession Number');
        $sheet->setCellValue('B10', 'Call Number');
        $sheet->setCellValue('C10', 'Title');
        $sheet->setCellValue('D10', 'Author');
        $sheet->setCellValue('E10', 'Last Inventory');
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->book->accession);
            $sheet->setCellValue('B' . $row, $item->book->call_number);
            $sheet->setCellValue('C' . $row, $item->book->title);
            $sheet->setCellValue('D' . $row, $item->book->author);
            $sheet->setCellValue('E' . $row, Carbon::parse($item->checked_at)->format('m/d/Y'));
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'inventory-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }
    /**
     * Generates data for the inventory report.
     *
     * @param Request $request
     * @param Inventory $model
     * @param bool $isExport
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function generateData(Request $request, Inventory $model, bool $isExport = false)
    {
        $startStr = $request->input('start');
        $endStr   = $request->input('end');
        $perPage  = $request->input('perPage', 10);

        $query = $model->newQuery()
            ->with('book:id,accession,call_number,title,author')
            ->select([
                'id',
                'book_id',
                'checked_at as date'
            ])
            ->whereHas('book')
            ->whereNotNull('checked_at');

        if ($startStr && $endStr) {
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = \Carbon\Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();

            $query->whereBetween('checked_at', [$startDate, $endDate]);
        }

        $query->orderBy('checked_at', 'desc')->orderBy('id', 'desc');

        if ($isExport) {
            $data = $query->get();

            if ($data->isNotEmpty()) {

                $max = $data->first()->date;
                $min = $data->last()->date;

                $data->reporting_period = \Carbon\Carbon::parse($min)->format('F j, Y') . ' to ' . \Carbon\Carbon::parse($max)->format('F j, Y');
            } else {
                $data->reporting_period = 'N/A';
            }
            $data->makeHidden(['id', 'book_id']);
            return $data;
        }

        $result = $query->paginate($perPage)->appends($request->all());
        $result->getCollection()->transform(function ($item) {
            return $item->makeHidden(['id', 'book_id']);
        });

        return $result;
    }
}
