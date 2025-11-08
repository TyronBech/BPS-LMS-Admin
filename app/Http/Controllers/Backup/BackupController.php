<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use App\Mail\BackupPasswordMail; // Make sure you've created this Mailable
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // added
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display a paginated list of all backup files.
     */
    public function index(Request $request)
    {
        // Pagination parameters
        $allowedPerPage = [10, 25, 50];
        $perPage = (int) $request->input('perPage', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $currentPage = (int) $request->input('page', 1);

        // Get all zip files from the storage
        $files = glob(storage_path('app/backups/BPS Library Management System/*.zip'));
        // Convert them into a collection with metadata
        $allBackups = new Collection($files);

        $backupsCollection = $allBackups->map(function ($file) {
            return [
                'filename' => basename($file),
                'type'     => 'Database',
                'size'     => round(filesize($file) / 1024 / 1024, 2) . ' MB',
                'created'  => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file), // For sorting
            ];
        })->sortByDesc('timestamp'); // Sort by the timestamp

        // Manually create a paginator
        $currentPageItems = $backupsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $backups = new LengthAwarePaginator(
            $currentPageItems,
            $backupsCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url()] // Set the path for the paginator links
        );

        // Append query string variables to pagination links
        $backups->appends($request->except('page'));

        return view('backup.index', compact('backups'));
    }

    /**
     * Create a new (unencrypted) database backup.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_token' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast-error', 'Invalid request!');
        }

        // Ensure backup target directory exists
        $dir = storage_path('app/backups/BPS Library Management System');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        try {
            Log::info("Starting database backup...");
            Artisan::call('backup:run --only-db');
            $output = Artisan::output();
            Log::debug("Backup output: " . $output);
            if (str_contains($output, 'Backup failed')) {
                throw new Exception($output);
            }
        } catch (Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            return back()->with('toast-error', 'Backup failed!');
        }

        $admin = Auth::guard('admin')->user();
        Notification::route('mail', $admin->email)
            ->notify(new \App\Notifications\BackupSucceeded($admin->first_name . ' ' . $admin->last_name));
        Log::info("Database backup completed!");
        return back()->with('toast-success', 'Database backup created successfully!');
    }

    /**
     * Encrypt a backup on-demand, email the password, and stream the download.
     */
    public function download(Request $request)
    {
        Log::debug('[Backup] download() called', [
            'route' => 'backup.download',
            'request_ip' => $request->ip(),
            'input' => ['filename' => $request->input('filename'), '_token_present' => $request->has('_token')],
        ]);

        $validator = Validator::make($request->all(), [
            '_token' => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);
        if ($validator->fails()) {
            Log::debug('[Backup] download() validation failed', ['errors' => $validator->errors()->toArray()]);
            return back()->with('toast-error', 'Invalid request!');
        }

        $tempExtractDir = null;
        $newSecuredPath = null;

        try {
            // 1. DEFINE PATHS
            $sourceZipPath = storage_path('app/backups/BPS Library Management System/' . $request->filename);
            Log::debug('[Backup] Resolved paths', [
                'sourceZipPath' => $sourceZipPath,
                'exists' => file_exists($sourceZipPath),
                'size_bytes' => file_exists($sourceZipPath) ? filesize($sourceZipPath) : null,
            ]);

            if (!file_exists($sourceZipPath)) {
                Log::debug('[Backup] Source zip not found');
                return back()->with('toast-error', 'Backup file not found!');
            }

            // Create a temp directory for our new secured zip
            $tempDir = storage_path('app/temp_backups');
            if (!file_exists($tempDir)) {
                @mkdir($tempDir, 0775, true);
                Log::debug('[Backup] Created temp dir', ['tempDir' => $tempDir, 'exists' => file_exists($tempDir)]);
            }

            $newSecuredFilename = 'secured_' . Str::random(10) . '_' . $request->filename;
            $newSecuredPath = $tempDir . '/' . $newSecuredFilename;
            Log::debug('[Backup] New secured zip target', ['newSecuredPath' => $newSecuredPath]);

            // 2. GENERATE UNIQUE PASSWORD
            $password = Str::random(12);
            $admin = Auth::guard('admin')->user();
            Log::debug('[Backup] Authenticated admin check', [
                'has_admin' => (bool) $admin,
                'admin_id' => $admin?->id,
                'admin_email' => $admin?->email,
            ]);
            if (!$admin) {
                throw new Exception("Could not find authenticated user to email.");
            }

            // 3. RE-ZIP WITH PASSWORD
            $tempExtractDir = storage_path('app/temp_backups/tmp_extract_' . Str::random(10));
            if (!file_exists($tempExtractDir)) {
                @mkdir($tempExtractDir, 0775, true);
                Log::debug('[Backup] Created temp extract dir', [
                    'tempExtractDir' => $tempExtractDir,
                    'exists' => file_exists($tempExtractDir)
                ]);
            }

            $newZip = new ZipArchive();
            $openNewZip = $newZip->open($newSecuredPath, ZipArchive::CREATE);
            Log::debug('[Backup] Opening new zip', ['result' => $openNewZip === TRUE ? 'OK' : $openNewZip]);
            if ($openNewZip !== TRUE) {
                throw new Exception("Could not create new zip file.");
            }

            $oldZip = new ZipArchive();
            $openOldZip = $oldZip->open($sourceZipPath);
            Log::debug('[Backup] Opening source zip', ['result' => $openOldZip === TRUE ? 'OK' : $openOldZip]);

            if ($openOldZip === TRUE) {
                $oldZip->extractTo($tempExtractDir);
                $oldZip->close();
                Log::debug('[Backup] Extracted source zip', [
                    'extractDir' => $tempExtractDir,
                ]);

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempExtractDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                $added = 0;
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($tempExtractDir) + 1);
                        $okAdd = $newZip->addFile($filePath, $relativePath);
                        $okEnc = $okAdd ? $newZip->setEncryptionName($relativePath, ZipArchive::EM_AES_256, $password) : false;
                        $added++;
                        if ($added <= 5) {
                            Log::debug('[Backup] Added file to secured zip', [
                                'relative' => $relativePath,
                                'add_ok' => (bool) $okAdd,
                                'enc_ok' => (bool) $okEnc,
                            ]);
                        }
                    }
                }
                Log::debug('[Backup] Finished adding files', ['added_count' => $added]);
            } else {
                $newZip->close(); // Close it before throwing
                throw new Exception("Could not open source zip file.");
            }

            $newZip->close();
            Log::debug('[Backup] Closed secured zip', [
                'newSecuredPath' => $newSecuredPath,
                'exists' => file_exists($newSecuredPath),
                'size_bytes' => file_exists($newSecuredPath) ? filesize($newSecuredPath) : null,
            ]);

            $this->deleteDirectory($tempExtractDir); // Clean up extraction dir
            Log::debug('[Backup] Cleaned up temp extraction dir', ['dir' => $tempExtractDir, 'exists' => is_dir($tempExtractDir)]);

            // 4. EMAIL THE PASSWORD
            try {
                $mailer = config('mail.default');
                Log::debug('[Backup] Sending password email', [
                    'to' => $admin->email,
                    'mailer' => $mailer,
                ]);
                Mail::to($admin->email)->send(new BackupPasswordMail($admin->first_name . ' ' . $admin->last_name, $password));
                Log::debug('[Backup] Password email sent');
            } catch (\Exception $e) {
                if (file_exists($newSecuredPath)) {
                    @unlink($newSecuredPath);
                    Log::debug('[Backup] Deleted secured zip after mail failure', ['path' => $newSecuredPath]);
                }
                Log::error('Mail send failed: ' . $e->getMessage());
                return back()->with('toast-error', 'Could not send password email. Backup aborted.');
            }

            // 5. STREAM THE NEW FILE AND DELETE IT
            Log::debug('[Backup] Streaming secured zip to client', [
                'download_name' => $request->filename,
                'path' => $newSecuredPath,
            ]);
            return response()->download($newSecuredPath, $request->filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Backup download/encryption failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'code' => $e->getCode(),
            ]);
            
            // Cleanup any temp files if they exist
            if ($newSecuredPath && file_exists($newSecuredPath)) {
                @unlink($newSecuredPath);
                Log::debug('[Backup] Deleted secured zip after failure', ['path' => $newSecuredPath]);
            }
            if ($tempExtractDir && is_dir($tempExtractDir)) {
                $this->deleteDirectory($tempExtractDir);
                Log::debug('[Backup] Deleted temp extract dir after failure', ['dir' => $tempExtractDir]);
            }

            return back()->with('toast-error', 'Backup download failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_token'   => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);

        if ($validator->fails()) {
            return back()->with('toast-error', 'Invalid request!');
        }
        try {
            $filePath = storage_path('app/backups/BPS Library Management System/' . $request->filename);

            if (!file_exists($filePath)) {
                return back()->with('toast-error', 'Backup file not found!');
            }

            unlink($filePath);

            return back()->with('toast-success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('toast-error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    /**
     * Recursively delete a directory.
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            (is_dir($path)) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}