<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class TransactionController extends Controller
{
    public function index()
    {
        $fromInputDate  = "";
        $toInputDate    = "";
        $inputName      = "";
        $inputLastName  = "";
        $data           = Transaction::with('books', 'users')
            ->orderBy(DB::raw('DATE(date_borrowed)'), 'desc')
            ->orderBy(DB::raw('TIME(date_borrowed)'), 'desc')
            ->get();
        return view('report.transactions.transactions' , compact('data', 'inputName', 'inputLastName', 'fromInputDate', 'toInputDate'));
    }
    public function test(){
        $data           = Transaction::with('books', 'users')
            ->orderBy(DB::raw('DATE(date_borrowed)'), 'desc')
            ->orderBy(DB::raw('TIME(date_borrowed)'), 'desc')
            ->get();
            return view('pdf.transaction-pdf-report-format', compact('data'));
    }
    public function search(Request $request)
    {
        $inputName      = $request->input('name');
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $validator = Validator::make($request->all(), [
            'start'         => 'sometimes',
            'end'           => 'sometimes',
            'last-name'     => 'sometimes',
            'first-name'    => 'sometimes',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if($request->input('submit') == 'pdf'){
            $data = $this->generateData($request);
            $this->generatePDF($data);
            return redirect()->route('report.transactions.transactions')->with('toast-success', 'Successfully exported to PDF');
        } else if($request->input('submit') == 'excel'){
            // $data = $this->generateData($request);
            // $this->exportExcel($data);
            return redirect()->route('report.transactions.transactions')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request);
        return view('report.transactions.transactions' , compact('data', 'inputName', 'fromInputDate', 'toInputDate'));
    }
    private function generatePDF($data)
    {
        $chunk      = $data->chunk(25);
        $arrayPdf   = array( 'data' => $chunk );
        $pdf        = Pdf::loadView('pdf.transaction-pdf-report-format', $arrayPdf);
        $directory  = 'C:/Users/tyron/Downloads';
        $pdf->save($directory . '/transactions-report_' . date('Y-m-d') . '.pdf');
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet(); 
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Log ID');
        $sheet->setCellValue('B1', 'RFID');
        $sheet->setCellValue('C1', 'Name');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'Time');
        $sheet->setCellValue('F1', 'Compute Use');
        $sheet->setCellValue('G1', 'Action');
        $row = 2;
        foreach($data as $item){
            $sheet->setCellValue('A' . $row, $item->id);
            $sheet->setCellValue('B' . $row, $item->users->rfid);
            $sheet->setCellValue('C' . $row, $item->users->last_name . ', ' . $item->users->first_name . ' ' . $item->users->middle_name);
            $sheet->setCellValue('D' . $row, Carbon::parse($item->timestamp)->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, Carbon::parse($item->timestamp)->format('H:i:s'));
            $sheet->setCellValue('F' . $row, $item->computer_use);
            $sheet->setCellValue('G' . $row, $item->action);
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $directory  = 'C:/Users/tyron/Downloads';
        $filename   = $directory . '/student-report_' . date('Y-m-d') . '.xlsx';
        $writer->save($filename);
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('name'));

        $query = Transaction::with('books', 'users');
        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(date_borrowed)'), [$fromInputDate, $toInputDate]);
        }

        if (strlen($inputName) > 0) {
            $query->whereHas('users', function ($q) use ($inputName) {
                $q->where('first_name', 'like', '%' . $inputName . '%');
                $q->orWhere('middle_name', 'like', '%' . $inputName . '%');
                $q->orWhere('last_name', 'like', '%' . $inputName . '%');
            })->orWhereHas('books', function ($q) use ($inputName) {
                $q->where('title', 'like', '%' . $inputName . '%')
                ->orWhere('accession', 'like', '%' . $inputName . '%');
            })->orWhere('transaction_type', 'like', '%' . $inputName . '%');
        }
        $data = $query->orderBy(DB::raw('DATE(date_borrowed)'), 'asc')
            ->get();
        return $data;
    }
}
