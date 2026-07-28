<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\LibraryClassReservation;
use App\Models\SystemSetting;
use App\Models\UISetting;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ClassReservationsController extends Controller
{
    public function index(Request $request)
    {
        $fromInputDate = $request->input('start', '');
        $toInputDate = $request->input('end', '');
        $perPage = $request->input('perPage', 10);
        $status = $request->input('status', 'All');

        Log::info('Class Reservations Report: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'start' => $fromInputDate,
                'end' => $toInputDate,
                'perPage' => $perPage,
                'status' => $status,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, false);

        return view('report.class-reservations.index', compact('data', 'fromInputDate', 'toInputDate', 'perPage', 'status'));
    }

    public function search(Request $request)
    {
        $fromInputDate = $request->input('start', '');
        $toInputDate = $request->input('end', '');
        $perPage = $request->input('perPage', 10);
        $status = $request->input('status', 'All');

        Log::info('Class Reservations Report: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'filters' => $request->only(['start', 'end', 'perPage', 'status']),
            'action' => $request->input('submit', 'search'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        if ($request->input('submit') === 'pdf') {
            $data = $this->generateData($request, true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->generatePDF($data);
            return redirect()->route('report.class-reservations')->with('toast-success', 'Successfully exported to PDF');
        }

        if ($request->input('submit') === 'excel') {
            $data = $this->generateData($request, true);
            if ($data->isEmpty()) {
                return redirect()->back()->with('toast-warning', 'No data available to be exported.')->withInput();
            }
            $this->exportExcel($data);
            return redirect()->route('report.class-reservations')->with('toast-success', 'Successfully exported to Excel');
        }

        $data = $this->generateData($request, false);

        return view('report.class-reservations.index', compact('data', 'fromInputDate', 'toInputDate', 'perPage', 'status'));
    }

    private function generateData(Request $request, $all)
    {
        $fromInputDate = $request->input('start');
        $toInputDate = $request->input('end');
        $status = $request->input('status', 'All');
        $perPage = $request->input('perPage', 10);

        $query = LibraryClassReservation::with('user', 'approver')->orderBy('created_at', 'desc');

        if (!empty($fromInputDate)) {
            $query->whereDate('reservation_date', '>=', $fromInputDate);
        }
        if (!empty($toInputDate)) {
            $query->whereDate('reservation_date', '<=', $toInputDate);
        }

        if ($status !== 'All') {
            $query->where('status', $status);
        }

        if ($all) {
            $data = $query->get();
        } else {
            $data = $query->paginate($perPage)->appends([
                'start' => $fromInputDate,
                'end' => $toInputDate,
                'perPage' => $perPage,
                'status' => $status,
            ]);
        }

        return $data;
    }

    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $logoPath = public_path('storage/' . ($settings->org_logo_img ?? ''));
        if (!file_exists($logoPath) || is_dir($logoPath)) {
            $logoPath = public_path('img/BPSLogo.png');
        }

        $items = [
            'title' => 'Tabular Presentation of Class Reservations Report',
            'school' => $settings->org_name ?? 'Bicutan Parochial School, Inc.',
            'address' => $settings->org_address ?? 'Manuel L. Quezon St., Lower Bicutan, Taguig City',
            'logo' => base64_encode(file_get_contents($logoPath)),
            'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'date' => 'as of ' . date('F j, Y'),
            'data' => $data,
            'totalCount' => $data->count(),
            'schoolYear' => \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'reservation_date')
        ];

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.class-reservation-pdf-report', $items));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('class-reservations-report ' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
    }

    private function exportExcel(Collection $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Class Reservations Report');

        $settings = UISetting::first() ?? new UISetting();
        $logoPath = public_path('storage/' . ($settings->org_logo_img ?? ''));
        if (!file_exists($logoPath) || is_dir($logoPath)) {
            $logoPath = public_path('img/BPSLogo.png');
        }

        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('B1');
            $drawing->setOffsetX(20);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheet);
        }

        $schoolName = $settings->org_name ?? 'Bicutan Parochial School, Inc.';
        $schoolAddress = $settings->org_address ?? 'Manuel L. Quezon St., Lower Bicutan, Taguig City';
        
        $sheet->setCellValue('C2', $schoolName);
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('C3', $schoolAddress);
        $sheet->getStyle('C3')->getFont()->setSize(10);

        $sheet->setCellValue('A6', 'Class Reservations Report');
        $sheet->mergeCells('A6:H6');
        $sheet->getStyle('A6:H6')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A6:H6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A8', 'Date Extracted: ' . date('Y-m-d'));
        $schoolYear = \App\Helpers\ReportHelper::getSchoolYear(request('start'), request('end'), $data, 'reservation_date');
        $sheet->setCellValue('D8', 'School Year: ' . $schoolYear);
        $sheet->setCellValue('H8', 'Prepared by: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $sheet->setCellValue('A10', 'Date');
        $sheet->setCellValue('B10', 'Time');
        $sheet->setCellValue('C10', 'Requestor Name');
        $sheet->setCellValue('D10', 'Purpose');
        $sheet->setCellValue('E10', 'Status');
        $sheet->setCellValue('F10', 'Submitted');
        $sheet->setCellValue('G10', 'Action Date');
        $sheet->setCellValue('H10', 'Remarks');

        $headerStyleArray = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FF1F4E78']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A10:H10')->applyFromArray($headerStyleArray);

        $row = 11;
        foreach ($data as $item) {
            $timeRange = \Carbon\Carbon::parse($item->start_time)->format('h:i A') . ($item->end_time ? ' - ' . \Carbon\Carbon::parse($item->end_time)->format('h:i A') : '');
            $requestor = $item->user ? $item->user->first_name . ' ' . $item->user->last_name : 'N/A';
            
            $actionDate = '';
            if ($item->status === 'Approved' && $item->approved_at) {
                $actionDate = $item->approved_at->format('M d, Y h:i A');
            } elseif ($item->status === 'Rejected' && $item->rejected_at) {
                $actionDate = $item->rejected_at->format('M d, Y h:i A');
            }

            $sheet->setCellValue('A' . $row, $item->reservation_date ? $item->reservation_date->format('M d, Y') : 'N/A');
            $sheet->setCellValue('B' . $row, $timeRange);
            $sheet->setCellValue('C' . $row, $requestor);
            $sheet->setCellValue('D' . $row, $item->purpose);
            $sheet->setCellValue('E' . $row, $item->status);
            $sheet->setCellValue('F' . $row, $item->created_at->format('M d, Y'));
            $sheet->setCellValue('G' . $row, $actionDate);
            $sheet->setCellValue('H' . $row, $item->remarks);
            
            $row++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $styleArray = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A10:H' . ($row - 1))->applyFromArray($styleArray);

        $fileName = 'class-reservations-report ' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new WriterXlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
