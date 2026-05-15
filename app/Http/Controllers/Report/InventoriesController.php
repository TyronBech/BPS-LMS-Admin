<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\UISetting;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use App\Models\Inventory;
use App\Models\SubjectAccessCode;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InventoriesController extends Controller
{
    private const INVENTORY_ACTIVE_KEY = 'inventory_cycle_active';

    public function index(Request $request)
    {
        $fromInputDate = $request->input('start', '');
        $toInputDate = $request->input('end', '');
        $perPage = $request->input('perPage', 10);
        $subjectId = $request->input('subject_id', 'All');

        Log::info('Inventory Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'start' => $fromInputDate,
                'end' => $toInputDate,
                'perPage' => $perPage,
                'subject_id' => $subjectId,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'perPage' => 'nullable|integer|min:1|max:500',
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

        $subjects = SubjectAccessCode::orderBy('access_code')->get();
        $data = $this->generateData($request, false);

        return view('report.inventories.index', compact('data', 'fromInputDate', 'toInputDate', 'perPage', 'subjects', 'subjectId'));
    }

    public function search(Request $request)
    {
        $fromInputDate = $request->input('start', '');
        $toInputDate = $request->input('end', '');
        $perPage = $request->input('perPage', 10);
        $subjectId = $request->input('subject_id', 'All');

        Log::info('Inventory Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['start', 'end', 'perPage', 'subject_id']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'perPage' => 'nullable|integer|min:1|max:500',
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

        if ($request->input('submit') === 'pdf') {
            $data = $this->generateData($request, true);
            $this->generatePDF($data);
            return redirect()->route('report.inventories')->with('toast-success', 'Successfully exported to PDF');
        }

        if ($request->input('submit') === 'excel') {
            $data = $this->generateData($request, true);
            $this->exportExcel($data);
            return redirect()->route('report.inventories')->with('toast-success', 'Successfully exported to Excel');
        }

        $subjects = SubjectAccessCode::orderBy('access_code')->get();
        $data = $this->generateData($request, false);

        return view('report.inventories.index', compact('data', 'fromInputDate', 'toInputDate', 'perPage', 'subjects', 'subjectId'));
    }

    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title' => 'Material Inventory Report',
            'school' => $settings->org_name ?? 'Bicutan Parochial School, Inc.',
            'address' => $settings->org_address ?? 'Manuel L. Quezon St., Lower Bicutan, Taguig City',
            'logo' => $settings->org_logo_full ?? base64_encode(file_get_contents(public_path('img/BPSLogoFull.png'))),
            'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date' => 'as of ' . date('F j, Y'),
            'data' => $data,
            'totalCount' => $data->count(),
        ];

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.inventory-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('inventory-report ' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }

    private function exportExcel(Collection $data)
    {
        $spreadsheet = new Spreadsheet();
        $logo = new Drawing();
        $settings = UISetting::first() ?? new UISetting();
        $sheet = $spreadsheet->getActiveSheet();

        $tempLogoPath = public_path('img/orgLogoFull.png');
        $decodedLogo = $settings->org_logo_full ? base64_decode($settings->org_logo_full) : null;
        if ($decodedLogo) {
            file_put_contents($tempLogoPath, $decodedLogo);
        }

        $logo->setName(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo->setDescription(($settings->org_initial ?? 'BPS') . ' Logo');
        $logo->setPath(($decodedLogo && file_exists($tempLogoPath)) ? $tempLogoPath : public_path('img/BPSLogoFull.png'));
        $logo->setHeight(80);
        $logo->setCoordinates('B1');
        $logo->setOffsetX(10);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Material Inventory Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->mergeCells('A6:D6');
        $sheet->setCellValue('A6', 'Material Inventory Report');
        $sheet->getStyle('A6:D6')->getFont()->setBold(true);
        $sheet->getStyle('A6:D6')->getFont()->setSize(14);
        $sheet->getStyle('A6:D6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:D6')->getAlignment()->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->mergeCells('A8:D8');
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:D8')->getFont()->setBold(true);
        $sheet->getStyle('A7:D8')->getFont()->setSize(10);
        $sheet->getStyle('A7:D8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:D8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:D8')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A9:D9');
        $sheet->setCellValue('A9', $data->reporting_period ?? 'Current inventory snapshot');
        $sheet->getStyle('A9:D9')->getFont()->setItalic(true);
        $sheet->getStyle('A9:D9')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A11:D11')->getFont()->setSize(10);
        $sheet->getStyle('A11:D11')->getFont()->setBold(true);
        $sheet->getStyle('A11:D11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:D11')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $sheet->setCellValue('A11', 'Accession Number');
        $sheet->setCellValue('B11', 'Author');
        $sheet->setCellValue('C11', 'Title');
        $sheet->setCellValue('D11', 'Description');
        $sheet->setCellValue('E11', 'Remarks');
        $row = 12;

        foreach ($data as $item) {
            $book = $item->book;
            
            $descArr = is_array($book->description) ? $book->description : json_decode($book->description, true);
            $descString = is_array($descArr) ? implode(', ', $descArr) : ($descArr ?? '');

            $sheet->setCellValue('A' . $row, $item->accession);
            $sheet->setCellValue('B' . $row, $item->author);
            $sheet->setCellValue('C' . $row, $item->title);
            $sheet->setCellValue('D' . $row, $descString);
            $sheet->setCellValue('E' . $row, $item->remarks);
            $sheet->getStyle('A' . $row . ':E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':E' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('A' . $row . ':E' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $row += 2;
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':D' . $row;
        $sheet->getStyle($styleRange)->getFont()->setBold(true);
        $sheet->getStyle($styleRange)->getFont()->setSize(10);
        $sheet->getStyle($styleRange)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($styleRange)->getAlignment()->setVertical('left');
        $sheet->getStyle($styleRange)->getAlignment()->setWrapText(true);

        $writer = new WriterXlsx($spreadsheet);
        $fileName = 'inventory-report ' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$fileName}\"");
        $writer->save('php://output');

        if ($decodedLogo && file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }

        exit;
    }

    private function generateData(Request $request, bool $isExport = false)
    {
        $startStr     = $request->input('start');
        $endStr       = $request->input('end');
        $barcode      = $request->input('barcode');
        $title        = $request->input('title');
        $category     = $request->input('category', 'All');
        $subjectId    = $request->input('subject_id', 'All');
        $perPage      = $request->input('perPage', 10);
        $inventoryActive = $this->isInventoryActive();

        $query = Inventory::whereHas('book', function($q) use ($title, $barcode, $category) {
            if ($barcode) {
                $q->where('barcode', 'like', "%{$barcode}%");
            }
            if ($title) {
                $q->where('title', 'like', "%{$title}%");
            }
            if ($category && $category !== 'All') {
                $q->where('category_id', $category);
            }
        })
        ->with(['book.category:id,name', 'cycle'])
        ->select([
            'id',
            'inventory_cycle_id',
            'book_id',
            'status',
            'remarks',
            'created_at',
            'is_scanned'
        ]);

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($subjectId && $subjectId !== 'All') {
            $query->whereHas('book.subjectAccessCodes', function($q) use ($subjectId) {
                $q->where('bk_subject_access_codes.id', $subjectId);
            });
        }

        if ($isExport) {
            $data = $query->get();
            foreach ($data as $item) {
                $book = $item->book;
                $remarksSelect = $inventoryActive
                    ? ($item->is_scanned == 0 ? 'Pending' : ($book->remarks ?? 'No Remarks'))
                    : ($book->remarks ?? 'No Remarks');
                $item->remarks = $remarksSelect;
                $item->accession = $book->accession;
                $item->title = $book->title;
                
                $authorsArr = is_array($book->authors) ? $book->authors : json_decode($book->authors, true);
                $item->author = $authorsArr['Main author'] ?? 'N/A';
            }
            $data->reporting_period = $this->buildReportingPeriod($request, $inventoryActive);
            return $data;
        }

        $paginated = $query->paginate($perPage)->appends($request->all());
        $paginated->getCollection()->each(function ($item) use ($inventoryActive) {
            $book = $item->book;
            $remarksSelect = $inventoryActive
                ? ($item->is_scanned == 0 ? 'Pending' : ($book->remarks ?? 'No Remarks'))
                : ($book->remarks ?? 'No Remarks');
            $item->remarks = $remarksSelect;
            $item->accession = $book->accession;
            $item->title = $book->title;

            $authorsArr = is_array($book->authors) ? $book->authors : json_decode($book->authors, true);
            $item->author = $authorsArr['Main author'] ?? 'N/A';
        });

        return $paginated;
    }

    private function buildReportingPeriod(Request $request, bool $inventoryActive): string
    {
        if ($request->filled('start') && $request->filled('end')) {
            $startDate = Carbon::parse($request->input('start'))->format('F j, Y');
            $endDate = Carbon::parse($request->input('end'))->format('F j, Y');

            return 'Checked between ' . $startDate . ' and ' . $endDate;
        }

        return $inventoryActive
            ? 'Current inventory in progress'
            : 'Current finished inventory';
    }

    private function isInventoryActive(): bool
    {
        $value = SystemSetting::where('key', self::INVENTORY_ACTIVE_KEY)->value('value');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
