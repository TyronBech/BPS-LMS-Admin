<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUserImageImport;
use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserImageImportController extends Controller
{
    /** Maximum file size in bytes (5 MB). */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /** Allowed image extensions. */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * Display the User Images import page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::info('User Image Import: Index page accessed', [
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        // Clear session data and temp files when visiting fresh (no pagination params)
        if (!$request->has('page') && !$request->has('perPage')) {
            $this->cleanupTempFiles($request);
            $request->session()->forget([
                'user_image_import_folder',
                'user_image_import_matched',
                'user_image_import_unmatched',
                'user_image_import_oversized',
            ]);
        }

        $showTable    = false;
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();

        return view('import.user-images.index', compact('showTable', 'activeImport'));
    }

    /**
     * Handle uploaded folder images and preview matched/unmatched files.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        Log::info('User Image Import: Upload initiated', [
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'has_files'  => $request->hasFile('images'),
            'timestamp'  => now(),
        ]);

        try {
            // If the user uploaded new files from the folder picker
            if ($request->isMethod('post') && $request->hasFile('images')) {
                // Clean up any previously uploaded temp files
                $this->cleanupTempFiles($request);

                $uploadedFiles = $request->file('images');
                $folderName    = 'Uploaded folder';

                // Derive folder name from the first file's relative path
                if (!empty($uploadedFiles)) {
                    $firstFile  = $uploadedFiles[0];
                    $clientPath = $firstFile->getClientOriginalName();
                    // The webkitRelativePath isn't available server-side, so use a hidden input or just the first file
                    $folderName = 'Selected folder';
                }

                // Filter to only allowed image extensions and store temporarily
                $storedFiles = [];
                foreach ($uploadedFiles as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                        continue;
                    }
                    $originalName = $file->getClientOriginalName();
                    $storedPath   = $file->storeAs('temp_user_images', $originalName);
                    $storedFiles[] = [
                        'stored_path'   => $storedPath,
                        'original_name' => $originalName,
                        'size'          => $file->getSize(),
                    ];
                }

                if (empty($storedFiles)) {
                    return redirect()
                        ->route('import.import-user-images')
                        ->with('toast-warning', 'No valid image files (.jpg, .jpeg, .png) found in the selected folder.');
                }

                // Categorize files
                [$matched, $unmatched, $oversized] = $this->categorizeUploadedFiles($storedFiles);

                // Store in session
                $request->session()->put('user_image_import_folder', $folderName);
                $request->session()->put('user_image_import_matched', $matched);
                $request->session()->put('user_image_import_unmatched', $unmatched);
                $request->session()->put('user_image_import_oversized', $oversized);
            }

            // Retrieve from session
            $folderName = $request->session()->get('user_image_import_folder');
            $matched    = $request->session()->get('user_image_import_matched', []);
            $unmatched  = $request->session()->get('user_image_import_unmatched', []);
            $oversized  = $request->session()->get('user_image_import_oversized', []);

            if (!$folderName) {
                return redirect()
                    ->route('import.import-user-images')
                    ->with('toast-warning', 'Please select a folder containing images to import.');
            }

            $showTable    = true;
            $hasMatched   = !empty($matched);
            $hasUnmatched = !empty($unmatched);
            $hasOversized = !empty($oversized);
            $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
            $perPage      = $request->input('perPage', 10);

            // Paginate matched data
            $matchedCurrentPage  = LengthAwarePaginator::resolveCurrentPage('matched');
            $matchedCurrentItems = array_slice($matched, ($matchedCurrentPage - 1) * $perPage, $perPage);
            $matchedPaginatedData = new LengthAwarePaginator($matchedCurrentItems, count($matched), $perPage, $matchedCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'matched',
            ]);

            // Paginate unmatched data
            $unmatchedCurrentPage  = LengthAwarePaginator::resolveCurrentPage('unmatched');
            $unmatchedCurrentItems = array_slice($unmatched, ($unmatchedCurrentPage - 1) * $perPage, $perPage);
            $unmatchedPaginatedData = new LengthAwarePaginator($unmatchedCurrentItems, count($unmatched), $perPage, $unmatchedCurrentPage, [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'unmatched',
            ]);

        } catch (\Exception $e) {
            Log::error('User Image Import: Upload failed', [
                'error_message' => $e->getMessage(),
                'user_id'       => Auth::id(),
            ]);
            return redirect()
                ->route('import.import-user-images')
                ->with('toast-error', 'An error occurred while processing the uploaded images: ' . $e->getMessage());
        }

        return view('import.user-images.index', compact(
            'showTable',
            'folderName',
            'matchedPaginatedData',
            'unmatchedPaginatedData',
            'hasMatched',
            'hasUnmatched',
            'hasOversized',
            'oversized',
            'perPage',
            'activeImport',
        ));
    }

    /**
     * Dispatch the user image import job to the queue.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Global one-active-import-at-a-time lock
        $activeImport = ImportProgress::whereIn('status', ['pending', 'processing'])->first();
        if ($activeImport) {
            $label = ucfirst($activeImport->type);
            return response()->json([
                'error'       => true,
                'message'     => "A {$label} import is already in progress. Please wait for it to finish before starting a new import.",
                'active_type' => $activeImport->type,
            ], 409);
        }

        $matched = $request->session()->get('user_image_import_matched', []);
        if (empty($matched)) {
            return response()->json([
                'error'   => true,
                'message' => 'No matched images found. Please upload a folder first.',
            ], 422);
        }

        $totalRows = count($matched);

        $progress = ImportProgress::create([
            'type'         => 'user_images',
            'status'       => 'pending',
            'initiated_by' => Auth::id(),
            'total_rows'   => $totalRows,
        ]);

        ProcessUserImageImport::dispatch($matched, $progress->id, Auth::id());

        $request->session()->forget([
            'user_image_import_folder',
            'user_image_import_matched',
            'user_image_import_unmatched',
            'user_image_import_oversized',
        ]);

        Log::info('User Image Import: Job dispatched', [
            'progress_id' => $progress->id,
            'total_files' => $totalRows,
            'user_id'     => Auth::id(),
        ]);

        return response()->json([
            'success'     => true,
            'progress_id' => $progress->id,
            'total_rows'  => $progress->total_rows,
        ]);
    }

    /**
     * Returns the current progress of a user image import job as JSON.
     *
     * @param Request $request
     * @param int     $id
     * @return \Illuminate\Http\JsonResponse
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
     * Categorize uploaded files into matched, unmatched, and oversized.
     *
     * Files are already stored in temp_user_images/ via Laravel Storage.
     * Each entry has: stored_path, original_name, size.
     *
     * @param array<int, array{stored_path: string, original_name: string, size: int}> $storedFiles
     * @return array{0: array, 1: array, 2: array} [matched, unmatched, oversized]
     */
    private function categorizeUploadedFiles(array $storedFiles): array
    {
        $matched   = [];
        $unmatched = [];
        $oversized = [];

        foreach ($storedFiles as $fileInfo) {
            $storedPath   = $fileInfo['stored_path'];
            $originalName = $fileInfo['original_name'];
            $fileSize     = $fileInfo['size'];
            $filename     = pathinfo($originalName, PATHINFO_FILENAME);
            $fullPath     = Storage::path($storedPath);

            // Check file size limit (5MB)
            if ($fileSize > self::MAX_FILE_SIZE) {
                $oversized[] = [
                    'path'      => $fullPath,
                    'filename'  => $originalName,
                    'id'        => $filename,
                    'size'      => $fileSize,
                    'size_text' => $this->formatFileSize($fileSize),
                ];
                continue;
            }

            // Look up user: student first, then employee
            $user     = null;
            $userType = null;

            $studentDetail = StudentDetail::where('id_number', $filename)->first();
            if ($studentDetail) {
                $user     = User::find($studentDetail->user_id);
                $userType = 'Student';
            }

            if (!$user) {
                $employeeDetail = EmployeeDetail::where('employee_id', $filename)->first();
                if ($employeeDetail) {
                    $user     = User::find($employeeDetail->user_id);
                    $userType = 'Employee';
                }
            }

            $entry = [
                'path'      => $fullPath,
                'filename'  => $originalName,
                'id'        => $filename,
                'size'      => $fileSize,
                'size_text' => $this->formatFileSize($fileSize),
            ];

            if ($user) {
                $entry['user_id']   = $user->id;
                $entry['user_name'] = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
                $entry['user_type'] = $userType;
                $matched[] = $entry;
            } else {
                $unmatched[] = $entry;
            }
        }

        return [$matched, $unmatched, $oversized];
    }

    /**
     * Clean up any previously stored temporary image files.
     *
     * @param Request $request
     * @return void
     */
    private function cleanupTempFiles(Request $request): void
    {
        // Don't clean up if there is an active import running, to avoid deleting files the job is using
        $activeImport = ImportProgress::where('type', 'user_images')
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($activeImport) {
            return;
        }

        if (Storage::exists('temp_user_images')) {
            Storage::deleteDirectory('temp_user_images');
        }
    }

    /**
     * Format a file size in bytes to a human-readable string.
     *
     * @param int $bytes
     * @return string
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}

