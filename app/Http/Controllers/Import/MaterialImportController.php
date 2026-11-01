<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMaterialImport;
use App\Models\ImportProgress;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\Imports\MaterialImportReader;
use App\Support\BookAcquisitionDetails;
use Illuminate\Support\Facades\Validator;
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

        if (!$request->has('page') && !$request->has('perPage') && !$request->boolean('preserve_import')) {
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

            $sessionEdits = $request->session()->get('material_import_edits', []);
            $data = $this->readMaterialsExcel(\Illuminate\Support\Facades\Storage::path($filePath), $sessionEdits);
            $acquisitionErrors = $this->acquisitionErrors($data);
            $researchCategoryNames = Category::all()->filter(fn ($category) => $category->isAcademicResearch())->map(fn ($category) => strtolower(trim($category->name)))->values()->all();

            // Query existing book accessions
            $accessions = [];
            foreach ($data as $item) {
                if (isset($item['accession'])) {
                    $accessions[] = strtoupper(trim($item['accession']));
                }
            }

            $existingAccessions = [];
            if (!empty($accessions)) {
                $dbAccessions = \App\Models\Book::whereIn('accession', $accessions)
                    ->pluck('accession')
                    ->toArray();
                $existingAccessions = array_flip(array_map('strtoupper', array_map('trim', $dbAccessions)));
            }

            // Partition into new and existing
            $newData = [];
            $existingData = [];
            foreach ($data as $index => $item) {
                $acc = isset($item['accession']) ? strtoupper(trim($item['accession'])) : '';
                if (isset($existingAccessions[$acc])) {
                    $existingData[$index] = $item;
                } else {
                    $newData[$index] = $item;
                }
            }

            $showTable   = true;
            $new         = !empty($newData);
            $existing    = !empty($existingData);
            $newCount    = count($newData);
            $existingCount = count($existingData);
            $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
            $perPage     = $request->input('perPage', 10);

            // Paginate New Data
            $newCurrentPage  = LengthAwarePaginator::resolveCurrentPage('new');
            $newCurrentItems = array_slice($newData, ($newCurrentPage - 1) * $perPage, $perPage, true);
            $newPaginatedData = new LengthAwarePaginator($newCurrentItems, $newCount, $perPage, $newCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'new',
            ]);

            // Paginate Existing Data
            $existingCurrentPage  = LengthAwarePaginator::resolveCurrentPage('existing');
            $existingCurrentItems = array_slice($existingData, ($existingCurrentPage - 1) * $perPage, $perPage, true);
            $existingPaginatedData = new LengthAwarePaginator($existingCurrentItems, $existingCount, $perPage, $existingCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'existing',
            ]);

            return view('import.materials.materials', [
                'showTable'             => $showTable,
                'newPaginatedData'      => $newPaginatedData,
                'existingPaginatedData' => $existingPaginatedData,
                'new'                   => $new,
                'existing'              => $existing,
                'newCount'              => $newCount,
                'existingCount'         => $existingCount,
                'perPage'               => $perPage,
                'activeImport'          => $activeImport,
                'acquisitionErrors'      => $acquisitionErrors,
                'researchCategoryNames'  => $researchCategoryNames,
            ]);

        } catch (\Exception $e) {
            Log::error('Material Import Error: ' . $e->getMessage());
            return redirect()->route('import.import-materials', ['preserve_import' => 1])->with('toast-error', $this->friendlyErrorMessage($e));
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
        $progress = null;

        // --- Global one-active-import-at-a-time lock ---
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
        if ($activeImport) {
            // Check if this is the SAME user starting the SAME import type and it was created very recently (e.g. within 2 mins)
            // This handles proxy timeouts and automatic retries gracefully.
            if ($activeImport->type === 'materials' && $activeImport->initiated_by === Auth::id() && $activeImport->created_at->gt(now()->subMinutes(2))) {
                return response()->json([
                    'success'     => true,
                    'progress_id' => $activeImport->id,
                    'total_rows'  => $activeImport->total_rows,
                ]);
            }

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

        try {
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

            // Keep corrections across failed attempts and validate the exact data passed to the worker.
            $request->session()->put('material_import_edits', $sessionEdits);
            $data = $this->readMaterialsExcel(\Illuminate\Support\Facades\Storage::path($filePath), $sessionEdits);
            $errors = $this->acquisitionErrors($data);
            if ($errors !== []) {
                return response()->json([
                    'error' => true,
                    'message' => reset($errors)[0],
                    'errors' => $errors,
                ], 422);
            }

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
        } catch (\Throwable $e) {
            if ($progress) {
                $progress->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('Material Import: Failed to dispatch job', [
                'error_message' => $e->getMessage(),
                'user_id'       => Auth::id(),
            ]);

            return response()->json([
                'error'   => true,
                'message' => $e instanceof \Exception ? $this->friendlyErrorMessage($e) : 'Unable to start the import.',
            ], 422);
        }
    }

    /**
     * Reads and parses raw materials data from Excel file.
     */
    private function readMaterialsExcel(string $fullPath, array $edits = []): array
    {
        ini_set('memory_limit', '1G');
        set_time_limit(300);

        return (new MaterialImportReader)->read($fullPath, $edits);
    }

    private function acquisitionErrors(array $rows): array
    {
        $categories = Category::all()->keyBy(fn ($category) => strtolower(trim($category->name)));
        $errors = [];
        foreach ($rows as $index => $item) {
            $categoryName = is_string($item['category'] ?? null) ? strtolower(trim($item['category'])) : '';
            $details = BookAcquisitionDetails::normalize($item, $categories->get($categoryName), false);
            $validator = Validator::make($details, BookAcquisitionDetails::rules());
            foreach ($validator->errors()->messages() as $field => $messages) {
                $row = $item['_excel_row'];
                $accession = is_scalar($item['accession'] ?? null) ? $item['accession'] : 'Unknown';
                $errors["materials.{$index}.{$field}"] = array_map(fn ($message) => "Excel row {$row} (accession: {$accession}): {$message}", $messages);
            }
        }

        return $errors;
    }

    /**
     * Returns the current progress of an import job as JSON.
     * Polled by the frontend every 1.5 seconds.
     *
     * Cross-references the `failed_jobs` table to detect jobs that were
     * killed by the queue worker (timeout, OOM) before the try-catch
     * inside handle() could update the progress record.
     */
    public function status(Request $request, int $id)
    {
        $progress = ImportProgress::find($id);

        if (!$progress) {
            return response()->json(['error' => true, 'message' => 'Import record not found.'], 404);
        }

        // Safety net: if the progress record is still active but the
        // underlying queue job has already been moved to failed_jobs,
        // mark the import as failed so the frontend can stop polling.
        if ($progress->isActive()) {
            $failedJob = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where(function ($query) use ($id) {
                    $query->where('payload', 'like', '%"progressId":' . $id . '%')
                        ->orWhere('payload', 'like', '%"progressId";i:' . $id . '%');
                })
                ->latest('failed_at')
                ->first();

            if ($failedJob) {
                $errorSnippet = \Illuminate\Support\Str::limit($failedJob->exception, 200);

                $progress->update([
                    'status'        => 'failed',
                    'error_message' => $progress->error_message
                        ?: 'The import job failed unexpectedly: ' . $errorSnippet,
                ]);
            }
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
