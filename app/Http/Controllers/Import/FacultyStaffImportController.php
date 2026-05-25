<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmployeeImport;
use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacultyStaffImportController extends Controller
{
    /**
     * Faculty/Staff Import: Index page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::info('Faculty/Staff Import: Index page accessed', [
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            $oldFile = $request->session()->get('employee_import_file');
            if ($oldFile) {
                if (\Illuminate\Support\Facades\Storage::exists($oldFile)) {
                    \Illuminate\Support\Facades\Storage::delete($oldFile);
                }
            }
            $request->session()->forget([
                'employee_import_file',
                'employee_import_edits_new',
                'employee_import_edits_existing',
            ]);
        }

        $showTable    = false;
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();

        return view('import.employees.index', compact('showTable', 'activeImport'));
    }

    /**
     * Dispatches the faculty/staff import to the queue.
     *
     * Returns a JSON response so the frontend progress overlay can poll.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // --- Global one-active-import-at-a-time lock ---
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
        if ($activeImport) {
            $label = ucfirst($activeImport->type);
            return response()->json([
                'error'       => true,
                'message'     => "A {$label} import is already in progress. Please wait for it to finish before starting a new import.",
                'active_type' => $activeImport->type,
            ], 409);
        }

        $filePath = $request->session()->get('employee_import_file');
        if (!$filePath || !\Illuminate\Support\Facades\Storage::exists($filePath)) {
            return response()->json([
                'error'   => true,
                'message' => 'No import file found. Please upload a file first.',
            ], 422);
        }

        // Merge any visible page edits
        $submittedNew = $request->input('new_employees', []);
        $sessionEditsNew = $request->session()->get('employee_import_edits_new', []);
        foreach ($submittedNew as $index => $employee) {
            $sessionEditsNew[$index] = array_merge($sessionEditsNew[$index] ?? [], $employee);
        }

        $submittedExisting = $request->input('existing_employees', []);
        $sessionEditsExisting = $request->session()->get('employee_import_edits_existing', []);
        foreach ($submittedExisting as $index => $employee) {
            $sessionEditsExisting[$index] = array_merge($sessionEditsExisting[$index] ?? [], $employee);
        }

        // Parse file to get total count
        [$newData, $existingData] = $this->readEmployeesExcel(\Illuminate\Support\Facades\Storage::path($filePath));
        $totalRows = count($newData) + count($existingData);

        $progress = ImportProgress::create([
            'type'         => 'employees',
            'status'       => 'pending',
            'initiated_by' => Auth::id(),
            'total_rows'   => $totalRows,
        ]);

        ProcessEmployeeImport::dispatch($filePath, $progress->id, Auth::id(), $sessionEditsNew, $sessionEditsExisting);

        $request->session()->forget([
            'employee_import_file',
            'employee_import_edits_new',
            'employee_import_edits_existing',
        ]);

        Log::info('Faculty/Staff Import: Job dispatched with file', [
            'progress_id' => $progress->id,
            'file_path'   => $filePath,
            'total_rows'  => $totalRows,
            'user_id'     => Auth::id(),
        ]);

        return response()->json([
            'success'     => true,
            'progress_id' => $progress->id,
            'total_rows'  => $progress->total_rows,
        ]);
    }

    /**
     * Handles the upload of faculties and staffs Excel file and stores the data in session.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        Log::info('Faculty/Staff Import: Upload process initiated', [
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'has_file'   => $request->hasFile('file'),
            'timestamp'  => now(),
        ]);

        try {
            if ($request->isMethod('post') && !$request->hasFile('file')) {
                // POST request for pagination, merge edits
                $submittedNew = $request->input('new_employees', []);
                $sessionEditsNew = $request->session()->get('employee_import_edits_new', []);
                foreach ($submittedNew as $index => $employee) {
                    $sessionEditsNew[$index] = array_merge($sessionEditsNew[$index] ?? [], $employee);
                }
                $request->session()->put('employee_import_edits_new', $sessionEditsNew);

                $submittedExisting = $request->input('existing_employees', []);
                $sessionEditsExisting = $request->session()->get('employee_import_edits_existing', []);
                foreach ($submittedExisting as $index => $employee) {
                    $sessionEditsExisting[$index] = array_merge($sessionEditsExisting[$index] ?? [], $employee);
                }
                $request->session()->put('employee_import_edits_existing', $sessionEditsExisting);
            } else if ($request->hasFile('file')) {
                // Storing new file
                $file = $request->file('file');
                $filePath = $file->store('temp_imports');
                $request->session()->put('employee_import_file', $filePath);
                $request->session()->forget([
                    'employee_import_edits_new',
                    'employee_import_edits_existing',
                ]);
            }

            $filePath = $request->session()->get('employee_import_file');
            if (!$filePath || !\Illuminate\Support\Facades\Storage::exists($filePath)) {
                return redirect()->route('import.import-faculties-staffs')->with('toast-warning', 'Please select a file to upload.');
            }

            // Parse file on the fly
            [$newData, $existingData] = $this->readEmployeesExcel(\Illuminate\Support\Facades\Storage::path($filePath));

            // Merge edits
            $sessionEditsNew = $request->session()->get('employee_import_edits_new', []);
            foreach ($sessionEditsNew as $index => $edit) {
                if (isset($newData[$index])) {
                    $newData[$index] = array_merge($newData[$index], $edit);
                }
            }

            $sessionEditsExisting = $request->session()->get('employee_import_edits_existing', []);
            foreach ($sessionEditsExisting as $index => $edit) {
                if (isset($existingData[$index])) {
                    $existingData[$index] = array_merge($existingData[$index], $edit);
                }
            }

            $showTable    = true;
            $new          = !empty($newData);
            $existing     = !empty($existingData);
            $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
            $perPage      = $request->input('perPage', 10);

            // Paginate New Data
            $newCurrentPage  = LengthAwarePaginator::resolveCurrentPage('new');
            $newCurrentItems = array_slice($newData, ($newCurrentPage - 1) * $perPage, $perPage);
            $newPaginatedData = new LengthAwarePaginator($newCurrentItems, count($newData), $perPage, $newCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'new',
            ]);

            // Paginate Existing Data
            $existingCurrentPage  = LengthAwarePaginator::resolveCurrentPage('existing');
            $existingCurrentItems = array_slice($existingData, ($existingCurrentPage - 1) * $perPage, $perPage);
            $existingPaginatedData = new LengthAwarePaginator($existingCurrentItems, count($existingData), $perPage, $existingCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'existing',
            ]);

        } catch (\Exception $e) {
            Log::error('Faculty/Staff Import: Upload process failed', [
                'error_message' => $e->getMessage(),
                'user_id'       => Auth::id(),
            ]);
            return redirect()->route('import.import-faculties-staffs')->with('toast-error', $this->friendlyErrorMessage($e));
        }

        return view('import.employees.index', compact(
            'showTable',
            'newPaginatedData',
            'existingPaginatedData',
            'new',
            'existing',
            'perPage',
            'activeImport',
        ));
    }

    /**
     * Reads and parses employee data from Excel, partitioned into new and existing.
     */
    private function readEmployeesExcel(string $fullPath): array
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $newData      = [];
        $existingData = [];

        if (empty($rows) || !isset($rows[0][0])) {
            throw new \Exception('Excel file is empty.');
        }

        for ($i = 18; $i < count($rows); $i++) {
            if (empty(array_filter(array_slice($rows[$i], 1, 7)))) {
                continue;
            }

            $fullName = $this->extractNameParts($rows[$i][2] ?? '');
            if (empty($fullName['first_name']) || empty($fullName['last_name'])) {
                throw new \Exception("Invalid format in row " . ($i + 1) . ". Please ensure that the 'Full Name' field are correctly filled.");
            }

            $temp = [
                'rfid'          => $rows[$i][1],
                'first_name'    => $fullName['first_name'],
                'middle_name'   => $fullName['middle_name'],
                'last_name'     => $fullName['last_name'],
                'suffix'        => $rows[$i][3],
                'gender'        => $rows[$i][4],
                'email'         => $rows[$i][5],
                'employee_id'   => $rows[$i][6],
                'employee_role' => $rows[$i][7],
            ];

            if (EmployeeDetail::where('employee_id', $temp['employee_id'])->exists()) {
                $existingData[] = $temp;
            } else {
                $newData[] = $temp;
            }
        }

        return [$newData, $existingData];
    }

    /**
     * Returns the current progress of an employee import job as JSON.
     */
    public function status(Request $request, int $id)
    {
        $progress = ImportProgress::find($id);

        if (!$progress) {
            return response()->json(['error' => true, 'message' => 'Import record not found.'], 404);
        }

        return response()->json([
            'id'             => $progress->id,
            'type'           => $progress->type,
            'status'         => $progress->status,
            'total_rows'     => $progress->total_rows,
            'processed_rows' => $progress->processed_rows,
            'new_count'      => $progress->new_count,
            'updated_count'  => $progress->updated_count,
            'percent'        => $progress->progressPercent(),
            'error_message'  => $progress->error_message,
        ]);
    }

    /**
     * Downloads the template for faculties and staffs import in Excel format.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Employee-template.xlsx');
        if (File::exists($filePath)) {
            return Response::download($filePath, 'Employee-template.xlsx');
        }
        abort(404, 'File not found.');
    }

    /**
     * Extracts the first name, middle name, last name, and suffix from a full name.
     *
     * @param string $fullName The full name of the employee.
     * @return array An associative array containing the first name, middle name, last name, and suffix.
     */
    private function extractNameParts(string $fullName): array
    {
        $suffixes     = ['Jr', 'Jr.', 'Sr', 'Sr.', 'II', 'III', 'IV', 'V', 'PhD', 'MD', 'Esq'];
        $normSuffixes = array_map(fn($s) => strtolower(rtrim($s, '.')), $suffixes);

        $parts      = explode(',', $fullName, 2);
        $lastName   = trim($parts[0] ?? '');
        $otherParts = trim($parts[1] ?? '');

        if ($otherParts === '') {
            return ['first_name' => '', 'middle_name' => '', 'last_name' => $lastName, 'suffix' => ''];
        }

        $namePieces  = preg_split('/\s+/', $otherParts);
        $firstName   = '';
        $middleName  = '';
        $suffix      = '';
        $suffixIndex = null;

        for ($i = 1; $i < count($namePieces); $i++) {
            $normalized = strtolower(rtrim($namePieces[$i], '.'));
            if (in_array($normalized, $normSuffixes, true)) {
                $suffixIndex = $i;
                $suffix      = $namePieces[$i];
                break;
            }
        }

        if ($suffixIndex !== null) {
            $firstName  = implode(' ', array_slice($namePieces, 0, $suffixIndex));
            $middleName = implode(' ', array_slice($namePieces, $suffixIndex + 1));
        } else {
            $firstName  = $namePieces[0] ?? '';
            $middleName = count($namePieces) > 1 ? implode(' ', array_slice($namePieces, 1)) : '';
        }

        return [
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'suffix'      => $suffix,
        ];
    }
}
