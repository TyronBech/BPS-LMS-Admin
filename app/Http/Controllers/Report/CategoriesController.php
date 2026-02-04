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

        $data = Category::select('legend', 'name', 'previous_inventory', 'newly_acquired', 'discarded', 'present_inventory')->get();
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

        $data = Category::select('legend', 'name', 'previous_inventory', 'newly_acquired', 'discarded', 'present_inventory')->get();
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
     * Generates a PDF report for the summary of BPS collections report.
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
        $dompdf->setPaper('A4', 'landscape');
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
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->mergeCells('A7:F7');
        $sheet->mergeCells('A8:F8');
        $sheet->setCellValue('A7', 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        $sheet->getStyle('A7:F8')->getFont()->setBold(true);
        $sheet->getStyle('A7:F8')->getFont()->setSize(10);
        $sheet->getStyle('A7:F8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:F8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:F8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:F10')->getFont()->setSize(12);
        $sheet->getStyle('A10:F10')->getFont()->setBold(true);
        $sheet->setCellValue('A10', 'Legend');
        $sheet->setCellValue('B10', 'Description');
        $sheet->setCellValue('C10', 'Previous Inventory');
        $sheet->setCellValue('D10', 'Newly Acquired');
        $sheet->setCellValue('E10', 'Discarded');
        $sheet->setCellValue('F10', 'Present Inventory');
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->legend);
            $sheet->setCellValue('B' . $row, $item->name);
            $sheet->setCellValue('C' . $row, $item->previous_inventory);
            $sheet->setCellValue('D' . $row, $item->newly_acquired);
            $sheet->setCellValue('E' . $row, $item->discarded);
            $sheet->setCellValue('F' . $row, $item->present_inventory);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal('left');
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setVertical('center');
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setSize(12);
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal('right');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data->sum('previous_inventory'));
        $sheet->setCellValue('D' . $row, $data->sum('newly_acquired'));
        $sheet->setCellValue('E' . $row, $data->sum('discarded'));
        $sheet->setCellValue('F' . $row, $data->sum('present_inventory'));
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
    public function update(){
        Log::info('Categories Report: Attempting to update summary matrix', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        try{
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
}
