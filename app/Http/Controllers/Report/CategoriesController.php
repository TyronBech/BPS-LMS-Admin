<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\UISetting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CategoriesController extends Controller
{
    /**
     * Page to display categories report.
     *
     * This function is used to fetch all categories from the database and pass it to the view.
     *
     * @param  Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        Log::info('Categories Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $data = $this->getSummaryCollectionData();
        return view('report.categories.categories', compact('data'));
    }
    /**
     * Handles the export request for categories report.
     *
     * This function is used to process the export request and generate the desired file type.
     *
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function export(Request $request)
    {
        Log::info('Categories Report: Export requested', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'export_type' => $request->input('submit'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $data = $this->getSummaryCollectionData();
        if ($request->input('submit') == 'pdf') {
            $this->generatePDF($data);
            return redirect()->back()->with('toast-success', 'PDF generated successfully');
        } else if ($request->input('submit') == 'excel') {
            $this->exportExcel($data);
            return redirect()->back()->with('toast-success', 'Excel generated successfully');
        }

        Log::warning('Categories Report: Invalid export type', [
            'user_id' => Auth::guard('admin')->id(),
            'input_type' => $request->input('submit'),
            'timestamp' => now(),
        ]);
        return redirect()->back()->with('toast-warning', 'Invalid export type');
    }
    /**
     * Generates a PDF report for the summary of collections report.
     *
     * @param Illuminate\Database\Eloquent\Collection $data The data to be included in the report.
     *
     * @return void
     */
    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => 'Summary of ' . ($settings->org_initial ?? 'BPS') . ' Collections Report',
            'school'        => $settings->org_name ?? "Bicutan Parochial School, Inc.",
            'address'       => $settings->org_address ?? "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'logo'          => $settings->org_logo_full ?? base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
            'user'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date'          => "as of " . date('F j, Y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.summary-pdf-report', $items));
        $dompdf->setPaper('legal', 'landscape');
        $dompdf->render();
        $dompdf->stream('book-summary-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the categories report data to an Excel file.
     *
     * @param  Illuminate\Database\Eloquent\Collection $data The data to be exported.
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

        $sheet->setTitle(($settings->org_initial ?? 'BPS') . ' Collection Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->mergeCells('A6:J6');
        $sheet->setCellValue('A6', 'Summary of ' . ($settings->org_initial ?? 'BPS') . ' Collections Report');
        $sheet->getStyle('A6:J6')->getFont()->setBold(true);
        $sheet->getStyle('A6:J6')->getFont()->setSize(14);
        $sheet->getStyle('A6:J6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:J6')->getAlignment()->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->mergeCells('A8:J8');
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:J8')->getFont()->setBold(true);
        $sheet->getStyle('A7:J8')->getFont()->setSize(10);
        $sheet->getStyle('A7:J8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:J8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:J8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:J10')->getFont()->setSize(10);
        $sheet->getStyle('A10:J10')->getFont()->setBold(true);
        $sheet->getStyle('A10:J10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10:J10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        $sheet->getStyle('A10:B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C10:J10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A10', 'Legend');
        $sheet->setCellValue('B10', 'Description');
        $sheet->setCellValue('C10', 'Previous Inventory');
        $sheet->setCellValue('D10', 'Newly Acquired');
        $sheet->setCellValue('E10', 'Lost and Paid For');
        $sheet->setCellValue('F10', 'Lost and Replaced');
        $sheet->setCellValue('G10', 'Unreturned');
        $sheet->setCellValue('H10', 'Missing');
        $sheet->setCellValue('I10', 'Discarded');
        $sheet->setCellValue('J10', 'Present Inventory');
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->legend);
            $sheet->setCellValue('B' . $row, $item->name);
            $sheet->setCellValue('C' . $row, $item->previous_inventory);
            $sheet->setCellValue('D' . $row, $item->newly_acquired);
            $sheet->setCellValue('E' . $row, $item->lost_and_paid_for);
            $sheet->setCellValue('F' . $row, $item->lost_and_replaced);
            $sheet->setCellValue('G' . $row, $item->unreturned);
            $sheet->setCellValue('H' . $row, $item->missing);
            $sheet->setCellValue('I' . $row, $item->discarded);
            $sheet->setCellValue('J' . $row, $item->present_inventory);
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row . ':J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setVertical('center');
            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setSize(12);
        $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('C' . $row . ':J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data->sum('previous_inventory'));
        $sheet->setCellValue('D' . $row, $data->sum('newly_acquired'));
        $sheet->setCellValue('E' . $row, $data->sum('lost_and_paid_for'));
        $sheet->setCellValue('F' . $row, $data->sum('lost_and_replaced'));
        $sheet->setCellValue('G' . $row, $data->sum('unreturned'));
        $sheet->setCellValue('H' . $row, $data->sum('missing'));
        $sheet->setCellValue('I' . $row, $data->sum('discarded'));
        $sheet->setCellValue('J' . $row, $data->sum('present_inventory'));

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:J' . $row)->applyFromArray($styleArray);

        $row += 2;
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':J' . $row;
        $sheet->getStyle($styleRange)->getFont()->setBold(true);
        $sheet->getStyle($styleRange)->getFont()->setSize(10);
        $sheet->getStyle($styleRange)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($styleRange)->getAlignment()->setVertical('left');
        $sheet->getStyle($styleRange)->getAlignment()->setWrapText(true);

        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'Collection-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }
    /**
     * Updates the summary matrix by calling a stored procedure.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        Log::info('Categories Report: Attempting to update summary matrix', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        try {
            DB::statement('CALL update_summary_matrix()');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Categories Report: Database error during update', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }

        Log::info('Categories Report: Summary matrix updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);
        return redirect()->back()->with('toast-success', 'Successfully updated');
    }

    /**
     * Builds summary report data with remark-based counters per category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getSummaryCollectionData(): Collection
    {
        return Category::query()
            ->select([
                'bk_categories.legend',
                'bk_categories.name',
                'bk_categories.previous_inventory',
                'bk_categories.newly_acquired',
                'bk_categories.present_inventory',
            ])
            ->selectSub(function ($query) {
                $query->from('bk_books')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('bk_books.deleted_at')
                    ->whereColumn('bk_books.category_id', 'bk_categories.id')
                    ->whereRaw('LOWER(bk_books.remarks) = ?', ['lost and paid for']);
            }, 'lost_and_paid_for')
            ->selectSub(function ($query) {
                $query->from('bk_books')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('bk_books.deleted_at')
                    ->whereColumn('bk_books.category_id', 'bk_categories.id')
                    ->whereRaw('LOWER(bk_books.remarks) = ?', ['lost and replaced']);
            }, 'lost_and_replaced')
            ->selectSub(function ($query) {
                $query->from('bk_books')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('bk_books.deleted_at')
                    ->whereColumn('bk_books.category_id', 'bk_categories.id')
                    ->whereRaw('LOWER(bk_books.remarks) = ?', ['unreturned']);
            }, 'unreturned')
            ->selectSub(function ($query) {
                $query->from('bk_books')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('bk_books.deleted_at')
                    ->whereColumn('bk_books.category_id', 'bk_categories.id')
                    ->whereRaw('LOWER(bk_books.remarks) = ?', ['missing']);
            }, 'missing')
            ->selectSub(function ($query) {
                $query->from('bk_books')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('bk_books.deleted_at')
                    ->whereColumn('bk_books.category_id', 'bk_categories.id')
                    ->whereRaw('LOWER(bk_books.remarks) = ?', ['discarded']);
            }, 'discarded')
            ->get();
    }
}
