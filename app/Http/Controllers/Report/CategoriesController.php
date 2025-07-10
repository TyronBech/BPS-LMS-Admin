<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    public function index()
    {
        $data = Category::all();
        return view('report.categories.categories', compact('data'));
    }
    public function export(Request $request)
    {
        $data = Category::all();
        if ($request->input('submit') == 'pdf') {
            $this->generatePDF($data);
            return redirect()->back()->with('toast-success', 'PDF generated successfully');
        } else if ($request->input('submit') == 'excel') {
            $this->exportExcel($data);
            return redirect()->back()->with('toast-success', 'Excel generated successfully');
        }
        return redirect()->back()->with('toast-warning', 'Invalid export type');
    }
    private function generatePDF($data)
    {
        $items = [
            'title'         => 'Summary of BPS Collections Report',
            'school'        => "Bicutan Parochial School, Inc.",
            'address'       => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
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
        $dompdf->loadHtml(view('pdf.summary-pdf-report', $items));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('book-summary-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Report');

        // Set column widths
        $columns = ['A' => 20, 'B' => 30, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 20];
        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Headers
        $headers = [
            'Legend',
            'Description',
            'Previous Inventory',
            'Newly Acquired',
            'Discarded',
            'Present Inventory'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF2F2F2'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDDDDDD'],
                    ],
                ],
            ]);
            $col++;
        }

        // Data rows
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->legend);
            $sheet->setCellValue('B' . $row, $item->name);
            $sheet->setCellValue('C' . $row, $item->previous_inventory);
            $sheet->setCellValue('D' . $row, $item->newly_acquired);
            $sheet->setCellValue('E' . $row, $item->discarded);
            $sheet->setCellValue('F' . $row, $item->present_inventory);

            foreach (range('A', 'F') as $colLetter) {
                $sheet->getStyle($colLetter . $row)->applyFromArray([
                    'font' => ['name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                ]);
            }

            $row++;
        }
        // Totals row
        $sheet->setCellValue('A' . $row, 'Total:');
        $sheet->mergeCells("A$row:B$row");
        $sheet->setCellValue("C$row", $data->sum('previous_inventory'));
        $sheet->setCellValue("D$row", $data->sum('newly_acquired'));
        $sheet->setCellValue("E$row", $data->sum('discarded'));
        $sheet->setCellValue("F$row", $data->sum('present_inventory'));

        // Apply bold style to totals
        foreach (range('A', 'F') as $colLetter) {
            $sheet->getStyle($colLetter . $row)->applyFromArray([
                'font' => ['bold' => true, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDDDDDD'],
                    ],
                ],
            ]);
        }
        // Export
        $writer = new WriterXlsx($spreadsheet);
        $fileName = 'summary-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit();
    }
    public function update(){
        try{
            DB::statement('CALL update_summary_matrix()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        return redirect()->back()->with('toast-success', 'Successfully updated');
    }
}
