<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMaterialImport;
use App\Models\ImportProgress;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MaterialImportController extends Controller
{
    /**
     * Index page of Material Import
     */
    public function index(Request $request)
    {
        Log::info('Material Import: Index page accessed', [
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            $oldFile = $request->session()->get('material_import_file');
            if ($oldFile) {
                if (\Illuminate\Support\Facades\Storage::exists($oldFile)) {
                    \Illuminate\Support\Facades\Storage::delete($oldFile);
                }
            }
            $request->session()->forget([
                'material_import_file',
                'material_import_edits',
            ]);
        }

        $showTable   = false;
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();

        return view('import.materials.materials', compact('showTable', 'activeImport'));
    }

    /**
     * Handles the upload of material data Excel file.
     */
    public function upload(Request $request)
    {
        try {
            if ($request->isMethod('post') && !$request->hasFile('file')) {
                // Merge submitted edits into the session data
                $submittedMaterials = $request->input('materials', []);
                $sessionEdits = $request->session()->get('material_import_edits', []);

                foreach ($submittedMaterials as $index => $material) {
                    if (!isset($sessionEdits[$index])) {
                        $sessionEdits[$index] = [];
                    }
                    if (isset($material['authors'])) {
                        $sessionEdits[$index]['authors'] = array_merge($sessionEdits[$index]['authors'] ?? [], $material['authors']);
                    }
                    if (isset($material['description'])) {
                        $sessionEdits[$index]['description'] = array_merge($sessionEdits[$index]['description'] ?? [], $material['description']);
                    }
                    $sessionEdits[$index] = array_merge(
                        $sessionEdits[$index],
                        array_diff_key($material, ['authors' => 1, 'description' => 1])
                    );
                }
                $request->session()->put('material_import_edits', $sessionEdits);
            } else if ($request->hasFile('file')) {
                // Storing new file
                $file = $request->file('file');
                $filePath = $file->store('temp_imports');
                $request->session()->put('material_import_file', $filePath);
                $request->session()->forget('material_import_edits');
            }

            $filePath = $request->session()->get('material_import_file');
            if (!$filePath || !\Illuminate\Support\Facades\Storage::exists($filePath)) {
                return redirect()->route('import.import-materials')->with('toast-warning', 'Please select a file to upload.');
            }

            // Parse file on the fly
            $data = $this->readMaterialsExcel(\Illuminate\Support\Facades\Storage::path($filePath));

            // Merge any edits from session
            $sessionEdits = $request->session()->get('material_import_edits', []);
            foreach ($sessionEdits as $index => $edit) {
                if (isset($data[$index])) {
                    if (isset($edit['authors'])) {
                        $data[$index]['authors'] = array_merge($data[$index]['authors'], $edit['authors']);
                    }
                    if (isset($edit['description'])) {
                        $data[$index]['description'] = array_merge($data[$index]['description'], $edit['description']);
                    }
                    $data[$index] = array_merge(
                        $data[$index],
                        array_diff_key($edit, ['authors' => 1, 'description' => 1])
                    );
                }
            }

            $showTable   = true;
            $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
            $perPage     = $request->input('perPage', 10);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
            $paginatedData = new LengthAwarePaginator($currentItems, count($data), $perPage, $currentPage, [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]);

            return view('import.materials.materials', [
                'showTable'    => $showTable,
                'data'         => $paginatedData,
                'perPage'      => $perPage,
                'activeImport' => $activeImport,
            ]);

        } catch (\Exception $e) {
            Log::error('Material Import Error: ' . $e->getMessage());
            return redirect()->route('import.import-materials')->with('toast-error', $this->friendlyErrorMessage($e));
        }
    }

    /**
     * Dispatches the material import to the queue.
     *
     * Returns a JSON response with the progress record ID so the frontend
     * can poll the status endpoint.
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

        $filePath = $request->session()->get('material_import_file');
        if (!$filePath || !\Illuminate\Support\Facades\Storage::exists($filePath)) {
            return response()->json([
                'error'   => true,
                'message' => 'No import file found. Please upload a file first.',
            ], 422);
        }

        // Merge any page-visible edits
        $submittedMaterials = $request->input('materials', []);
        $sessionEdits       = $request->session()->get('material_import_edits', []);

        foreach ($submittedMaterials as $index => $material) {
            if (!isset($sessionEdits[$index])) {
                $sessionEdits[$index] = [];
            }
            if (isset($material['authors'])) {
                $sessionEdits[$index]['authors'] = array_merge($sessionEdits[$index]['authors'] ?? [], $material['authors']);
            }
            if (isset($material['description'])) {
                $sessionEdits[$index]['description'] = array_merge($sessionEdits[$index]['description'] ?? [], $material['description']);
            }
            $sessionEdits[$index] = array_merge(
                $sessionEdits[$index],
                array_diff_key($material, ['authors' => 1, 'description' => 1])
            );
        }

        // Parse file to get row count
        $data = $this->readMaterialsExcel(\Illuminate\Support\Facades\Storage::path($filePath));

        // Create the progress record first
        $progress = ImportProgress::create([
            'type'         => 'materials',
            'status'       => 'pending',
            'initiated_by' => Auth::id(),
            'total_rows'   => count($data),
        ]);

        // Dispatch the job - passing file path and only the edits array
        ProcessMaterialImport::dispatch($filePath, $progress->id, Auth::id(), $sessionEdits);

        // Clear session
        $request->session()->forget([
            'material_import_file',
            'material_import_edits',
        ]);

        Log::info('Material Import: Job dispatched with file', [
            'progress_id' => $progress->id,
            'file_path'   => $filePath,
            'total_rows'  => count($data),
            'user_id'     => Auth::id(),
        ]);

        return response()->json([
            'success'     => true,
            'progress_id' => $progress->id,
            'total_rows'  => $progress->total_rows,
        ]);
    }

    /**
     * Reads and parses raw materials data from Excel file.
     */
    private function readMaterialsExcel(string $fullPath): array
    {
        ini_set('memory_limit', '1G');
        set_time_limit(300);

        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows) || !isset($rows[0][0])) {
            if (count($rows) <= 18) {
                throw new \Exception('Excel file is empty or template is incorrect.');
            }
        }

        $data           = [];
        $headerRowIndex = 17;
        $baseCol        = 0;

        if (isset($rows[$headerRowIndex][1]) && strtolower(trim((string) $rows[$headerRowIndex][1])) === 'accession') {
            $baseCol = 1;
        }

        for ($i = 18; $i < count($rows); $i++) {
            $isEmptyRow = true;
            for ($col = $baseCol; $col <= $baseCol + 23; $col++) {
                if (isset($rows[$i][$col]) && trim((string) $rows[$i][$col]) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                continue;
            }

            $data[] = [
                'accession'            => isset($rows[$i][$baseCol]) ? trim((string) $rows[$i][$baseCol]) : null,
                'title'                => isset($rows[$i][$baseCol + 1]) ? trim((string) $rows[$i][$baseCol + 1]) : null,
                'authors'              => [
                    'Main author'      => isset($rows[$i][$baseCol + 2]) ? trim((string) $rows[$i][$baseCol + 2]) : null,
                    'Corporate author' => isset($rows[$i][$baseCol + 3]) ? trim((string) $rows[$i][$baseCol + 3]) : null,
                    'Added authors'    => isset($rows[$i][$baseCol + 4]) ? trim((string) $rows[$i][$baseCol + 4]) : null,
                    'Contributors'     => isset($rows[$i][$baseCol + 5]) ? trim((string) $rows[$i][$baseCol + 5]) : null,
                ],
                'edition'              => isset($rows[$i][$baseCol + 6]) ? trim((string) $rows[$i][$baseCol + 6]) : null,
                'call_number'          => isset($rows[$i][$baseCol + 7]) ? trim((string) $rows[$i][$baseCol + 7]) : null,
                'isbn'                 => isset($rows[$i][$baseCol + 8]) ? trim((string) $rows[$i][$baseCol + 8]) : null,
                'description'          => [
                    'Description'   => isset($rows[$i][$baseCol + 9]) ? trim((string) $rows[$i][$baseCol + 9]) : null,
                    'Content notes' => isset($rows[$i][$baseCol + 10]) ? trim((string) $rows[$i][$baseCol + 10]) : null,
                    'Abstract'      => isset($rows[$i][$baseCol + 11]) ? trim((string) $rows[$i][$baseCol + 11]) : null,
                    'Reviews'       => isset($rows[$i][$baseCol + 12]) ? trim((string) $rows[$i][$baseCol + 12]) : null,
                    'Extent'        => isset($rows[$i][$baseCol + 13]) ? trim((string) $rows[$i][$baseCol + 13]) : null,
                    'Acc Material'  => isset($rows[$i][$baseCol + 14]) ? trim((string) $rows[$i][$baseCol + 14]) : null,
                ],
                'place_of_publication' => isset($rows[$i][$baseCol + 15]) ? trim((string) $rows[$i][$baseCol + 15]) : null,
                'publisher'            => isset($rows[$i][$baseCol + 16]) ? trim((string) $rows[$i][$baseCol + 16]) : null,
                'copyrights'           => isset($rows[$i][$baseCol + 17]) ? trim((string) $rows[$i][$baseCol + 17]) : null,
                'location'             => isset($rows[$i][$baseCol + 18]) ? trim((string) $rows[$i][$baseCol + 18]) : null,
                'languages'            => isset($rows[$i][$baseCol + 19]) ? trim((string) $rows[$i][$baseCol + 19]) : null,
                'book_type'            => isset($rows[$i][$baseCol + 20]) ? trim((string) $rows[$i][$baseCol + 20]) : 'Print',
                'category'             => isset($rows[$i][$baseCol + 21]) ? trim((string) $rows[$i][$baseCol + 21]) : null,
                'digital_copy_url'     => isset($rows[$i][$baseCol + 22]) ? trim((string) $rows[$i][$baseCol + 22]) : null,
                'subject'              => isset($rows[$i][$baseCol + 23]) ? trim((string) $rows[$i][$baseCol + 23]) : null,
            ];
        }

        return $data;
    }

    /**
     * Returns the current progress of an import job as JSON.
     * Polled by the frontend every 1.5 seconds.
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
     * Downloads the material import Excel template.
     */
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Material-template.xlsx');
        if (File::exists($filePath)) {
            return Response::download($filePath, 'Material-template.xlsx');
        }
        $oldPath = public_path('excel/Book-template.xlsx');
        if (File::exists($oldPath)) {
            return Response::download($oldPath, 'Material-template.xlsx');
        }
        abort(404, 'Template not found.');
    }
}
