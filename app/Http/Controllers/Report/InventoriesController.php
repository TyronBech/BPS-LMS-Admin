<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use DateTime;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Carbon\Carbon;

class InventoriesController extends Controller
{
    public function index(){
        $fromInputDate  = null;
        $toInputDate    = null;
        $data           = Inventory::with('book')->orderBy('created_at', 'desc')->get();
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data'));
    }
    public function search(Request $request){
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $start          = null;
        $end            = null;
        $data           = Inventory::with('book')->where('checked_at', '!=', null)->orderBy('created_at', 'desc')->get();
        if(strlen($fromInputDate) > 0) $start = DateTime::createFromFormat('m/d/Y', $request->input('start'))->format('Y-m-d');
        if(strlen($toInputDate) > 0) $end = DateTime::createFromFormat('m/d/Y', $request->input('end'))->format('Y-m-d');
        if(strlen($fromInputDate) > 0 || strlen($toInputDate) > 0){
            $data = Inventory::with('book')->where('checked_at', '!=', null)->whereBetween(DB::raw('DATE(bk_inventories.checked_at)'), [$start, $end])->get();
        }
        if ($request->input('submit') == 'pdf') {
            $this->generatePDF($data);
            return redirect()->back()->with('toast-success', 'PDF generated successfully');
        }
        if ($request->input('submit') == 'excel') {
            $this->exportExcel($data);
            return redirect()->back()->with('toast-success', 'Excel generated successfully');
        }
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data'));
    }
    private function generatePDF($data)
    {
        $items = [
            'title' => 'Inventory Report',
            'date' => date('m/d/y'),
            'data' => $data,
            'totalCount' => $data->count(),
        ];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('pdf.inventory-pdf-report', $items));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('users-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->setCellValue('A1', 'Accession Number');
        $sheet->setCellValue('B1', 'Call Number');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'Author');
        $sheet->setCellValue('E1', 'Last Inventory');
        $row = 2;
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
        exit();
    }
}
