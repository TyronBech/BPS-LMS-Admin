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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Dompdf\Dompdf;

class TransactionController extends Controller
{
    public function index()
    {
        $fromInputDate  = "";
        $toInputDate    = "";
        $inputName      = "";
        $inputLastName  = "";
        $data           = Transaction::with('book', 'user')
            ->orderBy(DB::raw('DATE(date_borrowed)'), 'desc')
            ->orderBy(DB::raw('TIME(date_borrowed)'), 'desc')
            ->get();
        return view('report.transactions.transactions', compact('data', 'inputName', 'inputLastName', 'fromInputDate', 'toInputDate'));
    }
    public function test()
    {
        $data           = Transaction::with('book', 'user')
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
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request);
            $this->generatePDF($data);
            return redirect()->route('report.transaction')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request);
            $this->exportExcel($data);
            return redirect()->route('report.transaction')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request);
        return view('report.transactions.transactions', compact('data', 'inputName', 'fromInputDate', 'toInputDate'));
    }
    private function generatePDF($data)
    {
        $items = [
            'title' => 'Transaction Report',
            'date' => date('m/d/y'),
            'data' => $data,
            'totalCount' => $data->count(),
        ];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('pdf.transaction-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('transaction-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaction Report');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->setCellValue('A1', 'Accession');
        $sheet->setCellValue('B1', 'Title');
        $sheet->setCellValue('C1', 'Name');
        $sheet->setCellValue('D1', 'Borrowed');
        $sheet->setCellValue('E1', 'Due');
        $sheet->setCellValue('F1', 'Returned');
        $row = 2;
        foreach ($data as $item) {
            if(!$item->book || !$item->user) {
                continue; // Skip if book or user relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->book->accession);
            $sheet->setCellValue('B' . $row, $item->book->title);
            $sheet->setCellValue('C' . $row, $item->user->last_name . ', ' . $item->user->first_name . ' ' . $item->user->middle_name);
            $sheet->setCellValue('D' . $row, $item->date_borrowed);
            $sheet->setCellValue('E' . $row, $item->due_date);
            $sheet->setCellValue('F' . $row, $item->return_date ?? 'Not Returned');
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'transaction-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit();
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('name'));

        $query = Transaction::with('book', 'user');
        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(date_borrowed)'), [$fromInputDate, $toInputDate]);
        }

        if (strlen($inputName) > 0) {
            $query->whereHas('user', function ($q) use ($inputName) {
                $q->where('first_name', 'like', '%' . $inputName . '%');
                $q->orWhere('middle_name', 'like', '%' . $inputName . '%');
                $q->orWhere('last_name', 'like', '%' . $inputName . '%');
                $q->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $inputName . '%');
                $q->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $inputName . '%');
                $q->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $inputName . '%');
                $q->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', '%' . $inputName . '%');
                $q->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', '%' . $inputName . '%');
            })->orWhereHas('book', function ($q) use ($inputName) {
                $q->where('title', 'like', '%' . $inputName . '%')
                    ->orWhere('accession', 'like', '%' . $inputName . '%');
            })->orWhere('transaction_type', 'like', '%' . $inputName . '%');
        }
        $data = $query->orderBy(DB::raw('DATE(date_borrowed)'), 'asc')
            ->get();
        return $data;
    }
}
