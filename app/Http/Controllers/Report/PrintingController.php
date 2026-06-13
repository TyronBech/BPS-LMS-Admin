<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Printing;
use App\Models\UISetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrintingController extends Controller
{
    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');
        $printingType   = $request->input('printing_type', 'all');

        $validator = Validator::make($request->all(), [
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'printing_type' => 'nullable|string|max:255',
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new Printing(), false);

        return view('report.printing.index', compact('data', 'search', 'userType', 'printingType', 'fromInputDate', 'toInputDate', 'perPage'));
    }

    public function search(Request $request)
    {
        $search         = $request->input('search', '');
        $userType       = $request->input('user_type', 'students');
        $printingType   = $request->input('printing_type', 'all');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'printing_type' => 'nullable|string|max:255',
            'perPage'       => 'nullable|integer|min:1|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, new Printing(), true);
            $this->generatePDF($data);
            return redirect()->route('report.printing')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, new Printing(), true);
            $this->exportExcel($data);
            return redirect()->route('report.printing')->with('toast-success', 'Successfully exported to Excel');
        }

        $data = $this->generateData($request, new Printing(), false);
        return view('report.printing.index', compact('data', 'search', 'userType', 'printingType', 'fromInputDate', 'toInputDate', 'perPage'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modal_user_type'   => 'required|in:student,faculty',
            'student_id'        => 'required_if:modal_user_type,student|nullable|exists:usr_users,id',
            'faculty_id'        => 'required_if:modal_user_type,faculty|nullable|exists:usr_users,id',
            'type'              => 'required|in:print,photocopy',
            'topic'             => 'required|string|max:255',
            'pages'             => 'required|integer|min:1',
            'title_of_material' => 'required_if:type,photocopy|nullable|string|max:255',
            'amount'            => 'required|numeric|min:0',
            'printed_at'        => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $printing = new Printing();
        $printing->type = $request->type;
        $printing->topic = $request->topic;
        $printing->pages = $request->pages;
        $printing->printed_at = $request->printed_at ?? now();

        if ($request->type === 'photocopy') {
            $printing->title_of_material = $request->title_of_material;
        } else {
            $printing->title_of_material = null;
        }
        $printing->amount = $request->amount;

        if ($request->modal_user_type === 'student') {
            $studentUser = User::with('students')->find($request->student_id);
            if (!$studentUser || !$studentUser->students) {
                return redirect()->back()->with('toast-warning', 'Invalid student selected.')->withInput();
            }
            $printing->student_id = $studentUser->students->id;
            $printing->faculty_id = null;
        } else {
            $facultyUser = User::with('employees')->find($request->faculty_id);
            if (!$facultyUser || !$facultyUser->employees) {
                return redirect()->back()->with('toast-warning', 'Invalid faculty/staff selected.')->withInput();
            }
            $printing->faculty_id = $facultyUser->employees->id;
            $printing->student_id = null;
        }

        $printing->save();

        return redirect()->back()->with('toast-success', 'Printing/Photocopy entry created successfully.');
    }

    public function searchUser(Request $request)
    {
        $search = $request->get('term');
        $typeParam = $request->get('type');
        
        $searchTerms = array_filter(explode(' ', $search));
        $users = User::where(function($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('middle_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%");
                });
            }
        });

        if ($typeParam === 'student') {
            $users->has('students');
        } else if ($typeParam === 'faculty') {
            $users->has('employees');
        } else {
            $users->where(function($query) {
                $query->has('students')->orHas('employees');
            });
        }
            
        $users = $users->limit(10)->get();

        $formatted_users = [];

        foreach ($users as $user) {
            $type = $user->students ? 'Student' : 'Faculty/Staff';
            $formatted_users[] = [
                'id' => $user->id,
                'text' => $user->first_name . ' ' . $user->last_name . ' (' . $type . ')'
            ];
        }

        return response()->json($formatted_users);
    }

    private function generatePDF(Collection $data)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $settings = UISetting::first() ?? new UISetting();
        $items = [
            'title'         => 'Printing & Photocopy Report',
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
        $dompdf->loadHtml(view('pdf.printing-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('printing-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
        exit;
    }

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
        $logo->setCoordinates('B1');
        $logo->setOffsetY(5);
        $logo->setWorksheet($sheet);

        $sheet->setTitle('Printing Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $sheet->mergeCells('A6:J6');
        $sheet->getStyle('A6:J6')->getFont()->setBold(true);
        $sheet->getStyle('A6:J6')->getFont()->setSize(14);
        $sheet->getStyle('A6:J6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:J6')->getAlignment()->setVertical('center');
        $sheet->setCellValue('A6', 'Printing & Photocopy Report');

        $sheet->getColumnDimension('A')->setWidth(15); // Date
        $sheet->getColumnDimension('B')->setWidth(15); // Time
        $sheet->getColumnDimension('C')->setWidth(20); // RFID
        $sheet->getColumnDimension('D')->setWidth(30); // User Name
        $sheet->getColumnDimension('E')->setWidth(25); // Grade & Section / Role
        $sheet->getColumnDimension('F')->setWidth(15); // Type
        $sheet->getColumnDimension('G')->setWidth(25); // Topic
        $sheet->getColumnDimension('H')->setWidth(30); // Title of Material
        $sheet->getColumnDimension('I')->setWidth(10); // Pages
        $sheet->getColumnDimension('J')->setWidth(15); // Amount
        
        $sheet->mergeCells('A7:J7');
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

        $sheet->setCellValue('A10', 'Date');
        $sheet->setCellValue('B10', 'Time');
        $sheet->setCellValue('C10', 'RFID');
        $sheet->setCellValue('D10', 'User Name');
        $sheet->setCellValue('E10', 'Grade & Section / Role');
        $sheet->setCellValue('F10', 'Type');
        $sheet->setCellValue('G10', 'Topic');
        $sheet->setCellValue('H10', 'Title of Material');
        $sheet->setCellValue('I10', 'Pages');
        $sheet->setCellValue('J10', 'Amount');
        
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, Carbon::parse($item->printed_at)->format('M j, Y'));
            $sheet->setCellValue('B' . $row, Carbon::parse($item->printed_at)->format('g:i A'));
            
            $rfid = 'N/A';
            if ($item->student && $item->student->users) {
                $rfid = $item->student->users->rfid ?? 'N/A';
                $sheet->setCellValue('D' . $row, $item->student->users->last_name . ', ' . $item->student->users->first_name . ' ' . $item->student->users->middle_name);
                $sheet->setCellValue('E' . $row, $item->student->level . ' - ' . $item->student->section);
            } elseif ($item->faculty && $item->faculty->users) {
                $rfid = $item->faculty->users->rfid ?? 'N/A';
                $sheet->setCellValue('D' . $row, $item->faculty->users->last_name . ', ' . $item->faculty->users->first_name . ' ' . $item->faculty->users->middle_name);
                $sheet->setCellValue('E' . $row, $item->faculty->employee_role);
            } else {
                $sheet->setCellValue('D' . $row, 'N/A');
                $sheet->setCellValue('E' . $row, 'N/A');
            }
            
            $sheet->setCellValue('C' . $row, $rfid);
            $sheet->setCellValue('F' . $row, ucfirst($item->type));
            $sheet->setCellValue('G' . $row, $item->topic);
            $sheet->setCellValue('H' . $row, $item->title_of_material ?? 'N/A');
            $sheet->setCellValue('I' . $row, $item->pages);
            
            if (isset($item->amount)) {
                $sheet->setCellValue('J' . $row, 'PHP ' . number_format($item->amount, 2));
            } else {
                $sheet->setCellValue('J' . $row, 'N/A');
            }

            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:J' . ($row - 1))->applyFromArray($styleArray);

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
        $fileName = 'printing-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }

    private function generateData(Request $request, Printing $model, bool $isExport = false)
    {
        $startStr       = $request->input('start');
        $endStr         = $request->input('end');
        $search         = strtolower($request->input('search'));
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');
        $printingType   = $request->input('printing_type', 'all');

        $query = $model->newQuery()
            ->with(['student.users', 'faculty.users']);

        if ($userType === 'students') {
            $query->whereNotNull('student_id');
        } elseif ($userType === 'employees') {
            $query->whereNotNull('faculty_id')->whereNull('student_id');
        }

        if ($printingType === 'print') {
            $query->where('type', 'print');
        } elseif ($printingType === 'photocopy') {
            $query->where('type', 'photocopy');
        }

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('printed_at', [$startDate, $endDate]);
        }

        if (strlen($search) > 0) {
            $searchTerms = array_filter(explode(' ', $search));
            $query->where(function($q) use ($searchTerms, $search) {
                $q->whereHas('student.users', function ($sub) use ($searchTerms) {
                    $sub->where(function ($queryWrapper) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $queryWrapper->where(function ($subQ) use ($term) {
                                $subQ->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                            });
                        }
                    });
                })->orWhereHas('faculty.users', function ($sub) use ($searchTerms) {
                    $sub->where(function ($queryWrapper) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $queryWrapper->where(function ($subQ) use ($term) {
                                $subQ->whereRaw('LOWER(first_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(middle_name) LIKE ?', ["%{$term}%"])
                                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$term}%"]);
                            });
                        }
                    });
                })->orWhereRaw('LOWER(topic) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(title_of_material) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderBy('printed_at', 'desc')->orderBy('id', 'desc');

        if ($isExport) {
            return $query->get();
        }

        return $query->paginate($perPage)->appends($request->all());
    }
}
