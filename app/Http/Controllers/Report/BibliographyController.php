<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\UISetting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class BibliographyController extends Controller
{
    public function index(Request $request)
    {
        $title = $request->input('title', '');
        $author = $request->input('author', '');
        $category = $request->input('category', '');
        $perPage = $request->input('perPage', 10);

        Log::info('Bibliography Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => $this->currentAdminName(),
            'filters' => [
                'title' => $title,
                'author' => $author,
                'category' => $category,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            Log::warning('Bibliography Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $categories = Category::orderBy('name')->get();
        $data = $this->generateData($request, new Book(), false);

        return view('report.bibliography.index', compact('data', 'title', 'author', 'category', 'perPage', 'categories'));
    }

    public function search(Request $request)
    {
        $title = $request->input('title', '');
        $author = $request->input('author', '');
        $category = $request->input('category', 'All');
        $perPage = $request->input('perPage', 10);
        $categoryOptions = Category::pluck('id')->map(fn ($id) => (string) $id)->prepend('All')->all();

        Log::info('Bibliography Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => $this->currentAdminName(),
            'filters' => $request->only(['title', 'author', 'category', 'perPage']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'category' => ['nullable', Rule::in($categoryOptions)],
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            Log::warning('Bibliography Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        if ($request->input('submit') === 'pdf') {
            Log::info('Bibliography Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            $data = $this->generateData($request, new Book(), true);
            $this->generatePDF($data);

            return redirect()->route('report.bibliography')->with('toast-success', 'Successfully exported to PDF');
        }

        if ($request->input('submit') === 'excel') {
            Log::info('Bibliography Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            $data = $this->generateData($request, new Book(), true);
            $this->exportExcel($data);

            return redirect()->route('report.bibliography')->with('toast-success', 'Successfully exported to Excel');
        }

        $data = $this->generateData($request, new Book(), false);
        $categories = Category::orderBy('name')->get();

        if (!count($data)) {
            Log::info('Bibliography Report: No data found for search', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->route('report.bibliography')->with('toast-error', 'No data found.');
        }

        return view('report.bibliography.index', compact('data', 'title', 'author', 'category', 'perPage', 'categories'));
    }

    private function generatePDF(Collection $data): void
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title' => 'Bibliography of Books',
            'school' => $settings->org_name ?? 'Bicutan Parochial School, Inc.',
            'address' => $settings->org_address ?? 'Manuel L. Quezon St., Lower Bicutan, Taguig City',
            'logo' => $this->resolveLogoBase64($settings),
            'user' => $this->currentAdminName(),
            'date' => 'as of ' . date('F j, Y'),
            'data' => $data,
            'totalCount' => $data->count(),
        ];

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.bibliography-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('bibliography-report ' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }

    private function exportExcel(Collection $data): void
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

        $sheet->setTitle('Bibliography Report');
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.3);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.3);

        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'Bibliography of Books');
        $sheet->getStyle('A6:B6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6:B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('A8:B8');
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->mergeCells('A9:B9');
        $sheet->setCellValue('A9', 'Total Entries: ' . $data->count());
        $sheet->getStyle('A8:B9')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A8:B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(115);

        $sheet->setCellValue('A11', 'No.');
        $sheet->setCellValue('B11', 'Bibliography Entry');
        $sheet->getStyle('A11:B11')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A11:B11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A11:B11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $row = 12;
        foreach ($data->values() as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->bibliography_entry);
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;
        }

        $lastDataRow = max(11, $row - 1);
        $sheet->getStyle('A11:B' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->freezePane('A12');

        $row += 2;
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . $this->currentAdminName());
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

        $writer = new WriterXlsx($spreadsheet);
        $fileName = 'bibliography-report ' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$fileName}\"");
        $writer->save('php://output');

        if ($decodedLogo && file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }

        exit;
    }

    private function generateData(Request $request, Book $model, bool $isExport = false)
    {
        $title = $request->input('title');
        $author = $request->input('author');
        $category = $request->input('category', 'All');
        $perPage = $request->input('perPage', 10);

        $query = $model->newQuery()
            ->with('category:id,name')
            ->whereHas('category')
            ->select([
                'id',
                'category_id',
                'title',
                'author',
                'place_of_publication',
                'publisher',
                'copyrights',
            ]);

        if ($title) {
            $query->where('title', 'like', "%{$title}%");
        }

        if ($author) {
            $query->where('author', 'like', "%{$author}%");
        }

        if ($category && $category !== 'All') {
            $query->where('category_id', $category);
        }

        $query->orderBy('title', 'asc')->orderBy('author', 'asc')->orderBy('id', 'asc');

        if ($isExport) {
            $data = $query->get();
            $data->transform(fn ($item) => $this->appendBibliographyEntry($item));
            $data->makeHidden(['id', 'category_id']);

            return $data;
        }

        $result = $query->paginate($perPage)->appends($request->all());
        $result->getCollection()->transform(fn ($item) => $this->appendBibliographyEntry($item));
        $result->getCollection()->each(fn ($item) => $item->makeHidden(['id', 'category_id']));

        return $result;
    }

    private function appendBibliographyEntry(Book $book): Book
    {
        $book->bibliography_entry = $this->formatBibliographyEntry($book);

        return $book;
    }

    private function formatBibliographyEntry(Book $book): string
    {
        $title = $this->normalizeBibliographyValue($book->title, 'Untitled');
        $author = $this->normalizeBibliographyValue($book->author);
        $place = $this->normalizeBibliographyValue($book->place_of_publication);
        $publisher = $this->normalizeBibliographyValue($book->publisher);
        $copyright = $this->normalizeBibliographyValue($book->copyrights);

        return "{$title}/by {$author}. -- {$place} : {$publisher}, {$copyright}.";
    }

    private function normalizeBibliographyValue(?string $value, string $fallback = 'N/A'): string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? preg_replace('/\s+/', ' ', $normalized) : $fallback;
    }

    private function currentAdminName(): string
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        if (!$admin) {
            return 'System';
        }

        return trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '')) ?: ($admin->full_name ?? 'System');
    }

    private function resolveLogoBase64(UISetting $settings): string
    {
        return $settings->org_logo_full ?: base64_encode(file_get_contents(public_path('img/BPSLogoFull.png')));
    }
}
