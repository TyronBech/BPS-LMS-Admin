<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class BookCirculationController extends Controller
{
    public function index()
    {
        $barcode        = "";
        $title          = "";
        $availability   = $this->extract_enums('bk_books', 'availability_status');
        $data           = Book::select('accession', 'call_number', 'title', 'barcode', 'availability_status', 'condition_status')->get();
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability'));
    }
    public function search(Request $request)
    {
        $barcode        = $request->input('barcode');
        $title          = $request->input('title');
        $availability   = $request->input('availability');
        $validator = Validator::make($request->all(), [
            'availability'  => 'sometimes',
            'title'         => 'sometimes',
            'barcode'       => 'sometimes',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        $data = $this->generateData($request);
        if($request->input('submit') == 'pdf'){
            $this->generatePDF($data);
            return redirect()->route('report.book-circulation')->with('toast-success', 'Successfully exported to PDF');
        } else if($request->input('submit') == 'excel'){
            $this->exportExcel($data);
            return redirect()->route('report.book-circulation')->with('toast-success', 'Successfully exported to Excel');
        }
        $availability = $this->extract_enums('bk_books', 'availability_status');
        if(!count($data)) return redirect()->route('report.book-circulation')->with('toast-error', 'No data found.');
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability'));
    }
    private function generatePDF($data)
    {
        $items = [
            'title'         => 'Book Report',
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
        $dompdf->loadHtml(view('pdf.book-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('book-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Book Report');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->setCellValue('A1', 'Accession');
        $sheet->setCellValue('B1', 'Call Number');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'Availability');
        $sheet->setCellValue('E1', 'Condition');
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->accession);
            $sheet->setCellValue('B' . $row, $item->call_number ?? 'N/A');
            $sheet->setCellValue('C' . $row, $item->title);
            $sheet->setCellValue('D' . $row, $item->availability_status);
            $sheet->setCellValue('E' . $row, $item->condition_status);
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'book-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit();
    }
    private function generateData(Request $request)
    {
        $barcode        = $request->input('barcode');
        $title          = strtolower($request->input('title'));
        $availability   = $request->input('availability');
        $query          = Book::select('accession', 'call_number', 'title', 'barcode', 'availability_status', 'condition_status');
        if (strlen($barcode) > 0) {
            $query->where('barcode', 'like', '%' . $barcode . '%');
        }
        if (strlen($title) > 0) {
            $query->where(DB::raw('lower(title)'), 'like', '%' . $title . '%');
        }
        if (strlen($availability) > 0 && $availability != 'Choose availability status') {
            $query->where('availability_status', $availability);
        }
        $data = $query->get();
        return $data;
    }
    private function extract_enums($table, $columnName){
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
        return $enumValues;
    }
}
