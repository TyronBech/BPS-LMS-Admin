<?php

namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use App\Models\Penalty;
use DateTime;

class PenaltiesController extends Controller
{
    public function index()
    {
        $inputName      = null;
        $fromInputDate  = null;
        $toInputDate    = null;
        $data = Penalty::with('penaltyRule')
            ->with('transaction.user')
            ->with('transaction.book')
            ->orderBy(DB::raw('DATE(created_at)'), 'desc')
            ->orderBy(DB::raw('TIME(created_at)'), 'desc')
            ->get();
        return view('report.penalties.index', compact('data', 'inputName', 'fromInputDate', 'toInputDate'));
    }
    public function search(Request $request)
    {
        $inputName      = $request->input('first-name');
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
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request);
            $this->exportExcel($data);
            return redirect()->route('report.penalties')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request);
        return view('report.penalties.index', compact('data', 'inputName', 'fromInputDate', 'toInputDate'));
    }
    private function generatePDF($data)
    {
        $items = [
            'title'         => 'Penalties Report',
            'school'        => "Bicutan Parochial School, Inc.",
            'address'       => "Manuel L. Quezon St., Lower Bicutan, Taguig City",
            'date'          => date('m/d/y'),
            'data'          => $data,
            'totalCount'    => $data->count(),
        ];
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.penalties-pdf-report', $items));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('penalties-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }
    private function exportExcel($data)
    {
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users Report');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Accession');
        $sheet->setCellValue('C1', 'Book');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'Penalty');
        $sheet->setCellValue('F1', 'Amount');
        $row = 2;
        foreach ($data as $item) {
            if(!$item->transaction->user || !$item->transaction->book || !$item->penaltyRule) {
                continue; // Skip if users relationship is not loaded
            }
            $sheet->setCellValue('A' . $row, $item->transaction->user->first_name . ' ' . $item->transaction->user->last_name);
            $sheet->setCellValue('B' . $row, $item->transaction->book->accession);
            $sheet->setCellValue('C' . $row, $item->transaction->book->title);
            $sheet->setCellValue('D' . $row, $item->transaction->date_borrowed);
            $sheet->setCellValue('E' . $row, $item->penaltyRule->type);
            $sheet->setCellValue('F' . $row, $item->amount);
            $row++;
        }
        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'penalties-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");
        exit();
    }
    private function generateData(Request $request)
    {
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $inputName      = strtolower($request->input('first-name'));

        $query = Penalty::with([
            'penaltyRule',
            'transaction.user',
            'transaction.book'
        ]);

        // Date filter (created_at column from penalties table)
        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $from = Carbon::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $to = Carbon::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');

            $query->whereBetween(DB::raw('DATE(tr_penalties.created_at)'), [$from, $to]);
        }

        // Name search through transaction.user
        if (!empty($inputName)) {
            $query->whereHas('transaction.user', function ($q) use ($inputName) {
                $q->where(DB::raw('LOWER(first_name)'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(middle_name)'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(CONCAT(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(CONCAT(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(CONCAT(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(CONCAT(last_name, ", ", first_name))'), 'like', '%' . $inputName . '%')
                ->orWhere(DB::raw('LOWER(CONCAT(first_name, " ", last_name))'), 'like', '%' . $inputName . '%');
            });
        }

        $data = $query->orderBy(DB::raw('DATE(tr_penalties.created_at)'), 'asc')->get();

        return $data;
    }
}
