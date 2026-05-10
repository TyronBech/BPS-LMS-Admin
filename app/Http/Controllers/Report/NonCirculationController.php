<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BkNonCirculation;
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

class NonCirculationController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->input('search', '');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $userType       = $request->input('user_type', 'students');

        $validator = Validator::make($request->all(), [
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new BkNonCirculation(), false);

        return view('report.non-circulations.index', compact('data', 'search', 'userType', 'fromInputDate', 'toInputDate', 'perPage'));
    }

    public function search(Request $request)
    {
        $search         = $request->input('search', '');
        $userType       = $request->input('user_type', 'students');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);

        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'search'        => 'nullable|string|max:255',
            'user_type'     => 'nullable|string|max:255',
            'perPage'       => 'nullable|integer|min:1|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, new BkNonCirculation(), true);
            $this->generatePDF($data);
            return redirect()->route('report.non-circulations')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, new BkNonCirculation(), true);
            $this->exportExcel($data);
            return redirect()->route('report.non-circulations')->with('toast-success', 'Successfully exported to Excel');
        }

        $data = $this->generateData($request, new BkNonCirculation(), false);
        return view('report.non-circulations.index', compact('data', 'search', 'userType', 'fromInputDate', 'toInputDate', 'perPage'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|exists:usr_users,id',
            'faculty_id' => 'required|exists:usr_users,id',
            'subject' => 'required|string|max:255',
            'borrowed_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $nonCirculation = new BkNonCirculation();
        $nonCirculation->subject = $request->subject;
        $nonCirculation->borrowed_at = $request->borrowed_at ?? now();

        if ($request->filled('student_id')) {
            $studentUser = User::with('students')->find($request->student_id);
            if (!$studentUser || !$studentUser->students) {
                return redirect()->back()->with('toast-warning', 'Invalid student selected.')->withInput();
            }
            $nonCirculation->student_id = $studentUser->students->id;
        }

        $facultyUser = User::with('employees')->find($request->faculty_id);
        if (!$facultyUser || !$facultyUser->employees) {
            return redirect()->back()->with('toast-warning', 'Invalid faculty/staff selected.')->withInput();
        }
        $nonCirculation->faculty_id = $facultyUser->employees->id;

        $nonCirculation->save();

        return redirect()->back()->with('toast-success', 'Non-Circulation entry created successfully.');
    }

    public function searchUser(Request $request)
    {
        $search = $request->get('term');
        $typeParam = $request->get('type');
        
        $users = User::where(function($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('CONCAT(last_name, ", ", first_name) LIKE ?', ["%{$search}%"]);
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
            'title'         => 'Non-Circulation Report',
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
        $dompdf->loadHtml(view('pdf.non-circulation-pdf-report', $items));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();
        $dompdf->stream('non-circulation-report ' . date('Y-m-d') . '.pdf', array('Attachment' => true));
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

        $sheet->setTitle('Non-Circulation Report');
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $sheet->mergeCells('A6:F6');
        $sheet->getStyle('A6:F6')->getFont()->setBold(true);
        $sheet->getStyle('A6:F6')->getFont()->setSize(14);
        $sheet->getStyle('A6:F6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:F6')->getAlignment()->setVertical('center');
        $sheet->setCellValue('A6', 'Non-Circulation Report');

        $sheet->getColumnDimension('A')->setWidth(15); // Date
        $sheet->getColumnDimension('B')->setWidth(15); // Time
        $sheet->getColumnDimension('C')->setWidth(30); // Student/Faculty
        $sheet->getColumnDimension('D')->setWidth(20); // Grade & Section / Role
        $sheet->getColumnDimension('E')->setWidth(30); // Subject
        $sheet->getColumnDimension('F')->setWidth(30); // Teacher
        
        $sheet->mergeCells('A7:F7');
        $sheet->mergeCells('A8:F8');
        
        $sheet->setCellValue('A8', 'Report Generated On: ' . date('F j, Y'));
        
        $sheet->getStyle('A7:F8')->getFont()->setBold(true);
        $sheet->getStyle('A7:F8')->getFont()->setSize(10);
        $sheet->getStyle('A7:F8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A7:F8')->getAlignment()->setVertical('left');
        $sheet->getStyle('A7:F8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A10:F10')->getFont()->setSize(10);
        $sheet->getStyle('A10:F10')->getFont()->setBold(true);
        $sheet->getStyle('A10:F10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10:F10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $sheet->setCellValue('A10', 'Date');
        $sheet->setCellValue('B10', 'Time');
        $sheet->setCellValue('C10', 'User Name');
        $sheet->setCellValue('D10', 'Grade & Section / Role');
        $sheet->setCellValue('E10', 'Subject');
        $sheet->setCellValue('F10', 'Teacher');
        
        $row = 11;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, Carbon::parse($item->borrowed_at)->format('M j, Y'));
            $sheet->setCellValue('B' . $row, Carbon::parse($item->borrowed_at)->format('g:i A'));
            
            if ($item->student && $item->student->users) {
                $sheet->setCellValue('C' . $row, $item->student->users->last_name . ', ' . $item->student->users->first_name . ' ' . $item->student->users->middle_name);
                $sheet->setCellValue('D' . $row, $item->student->level . ' - ' . $item->student->section);
                if ($item->faculty && $item->faculty->users) {
                    $sheet->setCellValue('F' . $row, $item->faculty->users->last_name . ', ' . $item->faculty->users->first_name);
                } else {
                    $sheet->setCellValue('F' . $row, 'N/A');
                }
            } elseif ($item->faculty && $item->faculty->users) {
                $sheet->setCellValue('C' . $row, $item->faculty->users->last_name . ', ' . $item->faculty->users->first_name . ' ' . $item->faculty->users->middle_name);
                $sheet->setCellValue('D' . $row, $item->faculty->employee_role);
                $sheet->setCellValue('F' . $row, 'N/A');
            }
            
            $sheet->setCellValue('E' . $row, $item->subject);

            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A10:F' . ($row - 1))->applyFromArray($styleArray);

        $row += 2;
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'Report Generated By: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name);

        $styleRange = 'A' . $row . ':F' . $row;
        $sheet->getStyle($styleRange)->getFont()->setBold(true);
        $sheet->getStyle($styleRange)->getFont()->setSize(10);
        $sheet->getStyle($styleRange)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($styleRange)->getAlignment()->setVertical('left');
        $sheet->getStyle($styleRange)->getAlignment()->setWrapText(true);

        $writer     = new WriterXlsx($spreadsheet);
        $fileName = 'non-circulation-report ' . date('Y-m-d') . '.xlsx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        $writer->save("php://output");

        if (file_exists($tempLogoPath)) {
            unlink($tempLogoPath);
        }
        exit;
    }

    private function generateData(Request $request, BkNonCirculation $model, bool $isExport = false)
    {
        $startStr   = $request->input('start');
        $endStr     = $request->input('end');
        $search     = strtolower($request->input('search'));
        $perPage    = $request->input('perPage', 10);
        $userType   = $request->input('user_type', 'students');

        $query = $model->newQuery()
            ->with(['student.users', 'faculty.users']);

        if ($userType === 'students') {
            $query->whereNotNull('student_id');
        } elseif ($userType === 'employees') {
            $query->whereNotNull('faculty_id')->whereNull('student_id');
        }

        if ($startStr && $endStr) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
            $query->whereBetween('borrowed_at', [$startDate, $endDate]);
        }

        if (strlen($search) > 0) {
            $query->where(function($q) use ($search) {
                $q->whereHas('student.users', function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(last_name, ", ", first_name)) LIKE ?', ["%{$search}%"]);
                })->orWhereHas('faculty.users', function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(CONCAT(last_name, ", ", first_name)) LIKE ?', ["%{$search}%"]);
                })->orWhereRaw('LOWER(subject) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderBy('borrowed_at', 'desc')->orderBy('id', 'desc');

        if ($isExport) {
            $data = $query->get();
            return $data;
        }

        return $query->paginate($perPage)->appends($request->all());
    }
}
