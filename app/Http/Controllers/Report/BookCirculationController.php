<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Dompdf\Dompdf;
use Dompdf\Options;

class BookCirculationController extends Controller
{
    /**
     * Page to display book circulation report.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $barcode        = $request->input('barcode', '');
        $title          = $request->input('title', '');
        $perPage        = $request->input('perPage', 10);
        $category       = $request->input('category', '');

        Log::info('Book Circulation Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $categories     = Category::all();
        $books          = new Book();
        $availability   = $this->extract_enums($books->getTable(), 'availability_status');
        $data           = $this->generateData($request, false);
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability', 'perPage', 'categories', 'category'));
    }
    /**
     * Processes the search request for book circulation report.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(Request $request)
    {
        $barcode        = $request->input('barcode', '');
        $title          = $request->input('title', '');
        $availability   = $request->input('availability', 'All');
        $category       = $request->input('category', 'All');
        $perPage        = $request->input('perPage', 10);
        $action         = $request->input('submit', 'search');

        Log::info('Book Circulation Report: Processing request', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'action' => $action,
            'filters' => [
                'barcode' => $barcode,
                'title' => $title,
                'availability' => $availability,
                'category' => $category,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $books          = new Book();
        $validator = Validator::make($request->all(), [
            'availability'  => 'nullable|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
            'title'         => 'nullable',
            'barcode'       => 'nullable',
            'perPage'       => 'nullable|numeric|in:10,25,50',
        ]);
        if($validator->fails()){
            Log::warning('Book Circulation Report: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if($request->input('submit') == 'pdf'){
            Log::info('Book Circulation Report: Generating PDF export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, true);
            $this->generatePDF($data);
            return redirect()->route('report.accession-list')->with('toast-success', 'Successfully exported to PDF');
        } else if($request->input('submit') == 'excel'){
            Log::info('Book Circulation Report: Generating Excel export', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            $data = $this->generateData($request, true);
            $this->exportExcel($data);
            return redirect()->route('report.accession-list')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, false);
        $categories = Category::all();
        $availability = $this->extract_enums($books->getTable(), 'availability_status');
        if(!count($data)) {
            Log::info('Book Circulation Report: No data found for search', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now()
            ]);
            return redirect()->route('report.accession-list')->with('toast-error', 'No data found.');
        }
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability', 'perPage', 'categories', 'category'));
    }
    /**
     * Generates a PDF report for the book circulation report.
     * 
     * @param  array  $data  The data to be included in the report.
     * 
     * @return void
     */
    private function generatePDF($data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);
        $items = [
            'title'         => 'Book Records',
            'school'        => "Bicutan Parochial School, Inc.",
            'address'       => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'logo'          => base64_encode(file_get_contents((public_path('img/BPSLogoFull.png')))),
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
        $dompdf->stream('book-records ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    /**
     * Exports the book circulation report to an Excel file.
     * 
     * @param  array  $data  The data to be included in the report.
     * 
     * @return void
     */
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $logo           = new Drawing();
        $sheet          = $spreadsheet->getActiveSheet();
        
        $logo->setName('BPS Logo');
        $logo->setDescription('BPS Logo');
        $logo->setPath(public_path('img/BPSLogoFull.png'));
        $logo->setHeight(80);
        $logo->setCoordinates('C1');
        $logo->setOffsetX(10);
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Accession List Report');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
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
        $sheet->setCellValue('A10', 'Accession');
        $sheet->setCellValue('B10', 'Call Number');
        $sheet->setCellValue('C10', 'Title');
        $sheet->setCellValue('D10', 'Category');
        $sheet->setCellValue('E10', 'Availability');
        $sheet->setCellValue('F10', 'Condition');
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->accession);
            $sheet->setCellValue('B' . $row, $item->call_number ?? 'N/A');
            $sheet->setCellValue('C' . $row, $item->title);
            $sheet->setCellValue('D' . $row, $item->category->name);
            $sheet->setCellValue('E' . $row, $item->availability_status);
            $sheet->setCellValue('F' . $row, $item->condition_status);
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'book-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit;
    }
    /**
     * Generates data for the book report.
     *
     * @param Request $request
     * @param bool $isExport
     * @return array
     */
    private function generateData(Request $request, bool $isExport = false)
    {
        $barcode        = strtolower($request->input('barcode', ''));
        $title          = strtolower($request->input('title', ''));
        $category       = $request->input('category', 'All');
        $availability   = strtolower($request->input('availability', 'All'));
        $perPage        = $request->input('perPage', 10);
        $query          = Book::with('category')->whereHas('category')->select('id', 'created_at', 'accession', 'call_number', 'title', 'barcode', 'availability_status', 'condition_status', 'category_id');
        if (strlen($barcode) > 0) {
            $query->where('barcode', 'like', '%' . $barcode . '%');
        }
        if (strlen($title) > 0) {
            $query->where(DB::raw('lower(title)'), 'like', '%' . $title . '%');
        }
        if (strlen($availability) > 0 && $availability != 'all') {
            $query->where(DB::raw('lower(availability_status)'), $availability);
        }
        if(strlen($category) > 0 && $category != 'All') {
            $query->where('category_id', $category);
        }
        $query->orderBy('accession', 'asc')->orderBy('id', 'asc');

        if ($isExport) {
            $data = $query->get();
        } else {
            $data = $query->paginate($perPage)->appends([
                'barcode'       => $barcode,
                'title'         => $title,
                'availability'  => $availability,
                'category'      => $category,
                'perPage'       => $perPage,
            ]);
        }
        return $data;
    }
    /**
     * Extracts the enum values from a given table and column name.
     * 
     * @param string $table The name of the table to query.
     * @param string $columnName The name of the column to extract the enum values from.
     * @return array An array of enum values. If no enum values are found, returns ['N/A'].
     */
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
        $enumValues = array_merge(['All'], $enumValues);
        return $enumValues;
    }
}
