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
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->generatePDF($data);
            return redirect()->route('report.inventories')->with('toast-success', 'Successfully exported to PDF');
        }

        if ($request->input('submit') === 'excel') {
            $data = $this->generateData($request, true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
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
            'title' => 'Tabular Presentation of Material Inventory Report',
            'school' => $settings->org_name ?? 'Bicutan Parochial School, Inc.',
            'address' => $settings->org_address ?? 'Manuel L. Quezon St., Lower Bicutan, Taguig City',
            'logo' => $settings->org_logo_full ?? base64_encode(file_get_contents(public_path('img/BPSLogoFull.png'))),
            'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date' => 'as of ' . date('F j, Y'),
            'data' => $data,
            'totalCount' => $data->count(),
            'schoolYear' => \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'created_at')
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
        $logo->setHeight(100);
        $logo->setCoordinates('C1');
        $logo->setOffsetX(-20);
        $logo->setOffsetY(1);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Material Inventory Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->mergeCells('A6:E6');
        $sheet->setCellValue('A6', 'Material Inventory Report');
        $sheet->getStyle('A6:E6')->getFont()->setBold(true);
        $sheet->getStyle('A6:E6')->getFont()->setSize(14);
        $sheet->getStyle('A6:E6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:E6')->getAlignment()->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->mergeCells('A7:E7');
        $sheet->setCellValue('A7', 'as of ' . date('F j, Y'));
        $sheet->getStyle('A7:E7')->getFont()->setBold(true);
        $sheet->getStyle('A7:E7')->getFont()->setSize(10);
        $sheet->getStyle('A7:E7')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A7:E7')->getAlignment()->setVertical('center');
        $sheet->getStyle('A7:E7')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A8:E8');
        $sheet->setCellValue('A8', '');
        $sheet->getStyle('A8:E8')->getFont()->setItalic(true);
        $sheet->getStyle('A8:E8')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A10:E10')->getFont()->setSize(10);
        $sheet->getStyle('A10:E10')->getFont()->setBold(true);
        $sheet->getStyle('A10:E10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10:E10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $sheet->setCellValue('A10', 'Accession Number');
        $sheet->setCellValue('B10', 'Author');
        $sheet->setCellValue('C10', 'Title');
        $sheet->setCellValue('D10', 'Description');
        $sheet->setCellValue('E10', 'Remarks');
        $row = 11;

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

        $lastDataRow = max(10, $row - 1);
        $sheet->getStyle('A10:E' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $row += 2;
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':E' . $row;
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

        $query = Inventory::whereHas('book', function ($q) use ($title, $barcode, $category) {
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
            ->with(['book.category:id,name'])
            ->select([
                'id',
                'book_id',
                'created_at',
                'checked_at',
                'is_scanned'
            ]);

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($subjectId && $subjectId !== 'All') {
            $query->whereHas('book.subjectAccessCodes', function ($q) use ($subjectId) {
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
        $period = \App\Helpers\ReportHelper::buildReportingPeriod(collect(), 'created_at');
        if ($period !== 'N/A') {
            return str_replace('From ', 'Checked between ', str_replace(' to ', ' and ', $period));
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
