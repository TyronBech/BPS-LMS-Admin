<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use App\Mail\BackupPasswordMail;
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
use Illuminate\Support\Facades\Notification;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display a paginated list of all backup files.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        Log::info('Backup: Index page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'user_email' => Auth::guard('admin')->user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Backup: Invalid pagination parameters', [
                'errors' => $validator->errors()->toArray(),
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        $currentPage = (int) $request->input('page', 1);

        Log::debug('Backup: Pagination parameters', [
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'user_id' => Auth::guard('admin')->id(),
        ]);

        // Get all zip files from the storage
        $backupPath = storage_path('app/backups/BPS Library Management System');
        $files = glob($backupPath . '/*.zip');

        Log::debug('Backup: Retrieved backup files', [
            'backup_path' => $backupPath,
            'total_files' => count($files),
            'user_id' => Auth::guard('admin')->id(),
        ]);

        // Convert them into a collection with metadata
        $allBackups = new Collection($files);
        $backupsCollection = $allBackups->map(function ($file) {
            return [
                'filename' => basename($file),
                'type'     => 'Database',
                'size'     => round(filesize($file) / 1024 / 1024, 2) . ' MB',
                'created'  => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file),
            ];
        })->sortByDesc('timestamp');

        // Manually create a paginator
        $currentPageItems = $backupsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $backups = new LengthAwarePaginator(
            $currentPageItems,
            $backupsCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );

        Log::info('Backup: List viewed successfully', [
            'total_backups' => $backupsCollection->count(),
            'displayed_backups' => $currentPageItems->count(),
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        $backups->appends($request->except('page'));
        return view('backup.index', compact('backups'));
    }

    /**
     * Create a new (unencrypted) database backup.
     */
    public function create(Request $request)
    {
        Log::info('Backup: Create backup initiated', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'user_email' => Auth::guard('admin')->user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            '_token' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Backup: Invalid request token', [
                'error_message' => $validator->errors()->first(),
                'user_id' => Auth::guard('admin')->id(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return back()->with('toast-error', 'Invalid request!');
        }

        // Ensure backup target directory exists
        $dir = storage_path('app/backups/BPS Library Management System');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);

            Log::info('Backup: Created backup directory', [
                'directory' => $dir,
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
        }

        try {
            Log::info('Backup: Starting database backup process', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            Artisan::call('backup:run --only-db');
            $output = Artisan::output();

            Log::debug('Backup: Artisan backup command output', [
                'output' => $output,
                'user_id' => Auth::guard('admin')->id(),
            ]);

            if (str_contains($output, 'Backup failed')) {
                Log::error('Backup: Database backup failed', [
                    'output' => $output,
                    'user_id' => Auth::guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                throw new Exception($output);
            }

            $admin = Auth::guard('admin')->user();
            Notification::route('mail', $admin->email)
                ->notify(new \App\Notifications\BackupSucceeded($admin->first_name . ' ' . $admin->last_name));

            Log::info('Backup: Database backup completed successfully', [
                'user_id' => $admin->id,
                'user_name' => $admin->full_name,
                'notification_sent' => true,
                'timestamp' => now(),
            ]);

            return back()->with('toast-success', 'Database backup created successfully!');

        } catch (Exception $e) {
            Log::error('Backup: Database backup failed with exception', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return back()->with('toast-error', 'Backup failed!');
        }
    }

    /**
     * Encrypt a backup on-demand, email the password, and stream the download.
     */
    public function download(Request $request)
    {
        Log::info('Backup: Download requested', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'user_email' => Auth::guard('admin')->user()->email,
            'filename' => $request->input('filename'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            '_token' => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);

        if ($validator->fails()) {
            Log::warning('Backup: Download validation failed', [
                'errors' => $validator->errors()->toArray(),
                'filename' => $request->input('filename'),
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return back()->with('toast-error', 'Invalid request!');
        }

        $tempExtractDir = null;
        $newSecuredPath = null;

        try {
            // 1. DEFINE PATHS
            $sourceZipPath = storage_path('app/backups/BPS Library Management System/' . $request->filename);

            Log::debug('Backup: Resolving file paths', [
                'source_path' => $sourceZipPath,
                'file_exists' => file_exists($sourceZipPath),
                'file_size_mb' => file_exists($sourceZipPath) ? round(filesize($sourceZipPath) / 1024 / 1024, 2) : null,
                'user_id' => Auth::guard('admin')->id(),
            ]);

            if (!file_exists($sourceZipPath)) {
                Log::error('Backup: Source file not found', [
                    'filename' => $request->filename,
                    'path' => $sourceZipPath,
                    'user_id' => Auth::guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                return back()->with('toast-error', 'Backup file not found!');
            }

            // Create a temp directory for our new secured zip
            $tempDir = storage_path('app/temp_backups');
            if (!file_exists($tempDir)) {
                @mkdir($tempDir, 0775, true);

                Log::debug('Backup: Created temporary directory', [
                    'temp_dir' => $tempDir,
                    'user_id' => Auth::guard('admin')->id(),
                ]);
            }

            $newSecuredFilename = 'secured_' . Str::random(10) . '_' . $request->filename;
            $newSecuredPath = $tempDir . '/' . $newSecuredFilename;

            Log::debug('Backup: Generated secured filename', [
                'secured_filename' => $newSecuredFilename,
                'secured_path' => $newSecuredPath,
                'user_id' => Auth::guard('admin')->id(),
            ]);

            // 2. GENERATE UNIQUE PASSWORD
            $password = Str::random(12);
            $admin = Auth::guard('admin')->user();

            if (!$admin) {
                Log::error('Backup: Admin user not found', [
                    'timestamp' => now(),
                ]);

                throw new Exception("Could not find authenticated user to email.");
            }

            Log::debug('Backup: Generated encryption password', [
                'password_length' => strlen($password),
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
            ]);

            // 3. RE-ZIP WITH PASSWORD
            $tempExtractDir = storage_path('app/temp_backups/tmp_extract_' . Str::random(10));
            if (!file_exists($tempExtractDir)) {
                @mkdir($tempExtractDir, 0775, true);

                Log::debug('Backup: Created extraction directory', [
                    'extract_dir' => $tempExtractDir,
                    'user_id' => $admin->id,
                ]);
            }

            $newZip = new ZipArchive();
            $openNewZip = $newZip->open($newSecuredPath, ZipArchive::CREATE);

            Log::debug('Backup: Opening new secured zip', [
                'result' => $openNewZip === TRUE ? 'SUCCESS' : 'FAILED',
                'error_code' => $openNewZip !== TRUE ? $openNewZip : null,
                'user_id' => $admin->id,
            ]);

            if ($openNewZip !== TRUE) {
                Log::error('Backup: Could not create new secured zip', [
                    'error_code' => $openNewZip,
                    'path' => $newSecuredPath,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);

                throw new Exception("Could not create new zip file.");
            }

            $oldZip = new ZipArchive();
            $openOldZip = $oldZip->open($sourceZipPath);

            Log::debug('Backup: Opening source zip', [
                'result' => $openOldZip === TRUE ? 'SUCCESS' : 'FAILED',
                'error_code' => $openOldZip !== TRUE ? $openOldZip : null,
                'user_id' => $admin->id,
            ]);

            if ($openOldZip === TRUE) {
                $oldZip->extractTo($tempExtractDir);
                $oldZip->close();

                Log::info('Backup: Source zip extracted successfully', [
                    'extract_dir' => $tempExtractDir,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempExtractDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                $added = 0;
                $encrypted = 0;
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($tempExtractDir) + 1);
                        $okAdd = $newZip->addFile($filePath, $relativePath);
                        $okEnc = $okAdd ? $newZip->setEncryptionName($relativePath, ZipArchive::EM_AES_256, $password) : false;

                        if ($okAdd) $added++;
                        if ($okEnc) $encrypted++;

                        if ($added <= 5) {
                            Log::debug('Backup: File added and encrypted', [
                                'relative_path' => $relativePath,
                                'add_success' => (bool) $okAdd,
                                'encryption_success' => (bool) $okEnc,
                                'user_id' => $admin->id,
                            ]);
                        }
                    }
                }

                Log::info('Backup: All files processed', [
                    'total_added' => $added,
                    'total_encrypted' => $encrypted,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);
            } else {
                $newZip->close();

                Log::error('Backup: Could not open source zip for extraction', [
                    'error_code' => $openOldZip,
                    'source_path' => $sourceZipPath,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);

                throw new Exception("Could not open source zip file.");
            }

            $newZip->close();

            Log::info('Backup: Secured zip created successfully', [
                'secured_path' => $newSecuredPath,
                'file_exists' => file_exists($newSecuredPath),
                'file_size_mb' => file_exists($newSecuredPath) ? round(filesize($newSecuredPath) / 1024 / 1024, 2) : null,
                'user_id' => $admin->id,
                'timestamp' => now(),
            ]);

            $this->deleteDirectory($tempExtractDir);

            Log::debug('Backup: Cleaned up extraction directory', [
                'extract_dir' => $tempExtractDir,
                'user_id' => $admin->id,
            ]);

            // 4. EMAIL THE PASSWORD
            try {
                Log::info('Backup: Sending password email', [
                    'recipient_email' => $admin->email,
                    'recipient_name' => $admin->full_name,
                    'mailer' => config('mail.default'),
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);

                Mail::to($admin->email)->send(new BackupPasswordMail($admin->first_name . ' ' . $admin->last_name, $password));

                Log::info('Backup: Password email sent successfully', [
                    'recipient_email' => $admin->email,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                if (file_exists($newSecuredPath)) {
                    @unlink($newSecuredPath);

                    Log::debug('Backup: Deleted secured zip after mail failure', [
                        'path' => $newSecuredPath,
                        'user_id' => $admin->id,
                    ]);
                }

                Log::error('Backup: Failed to send password email', [
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'recipient_email' => $admin->email,
                    'user_id' => $admin->id,
                    'timestamp' => now(),
                ]);

                return back()->with('toast-error', 'Could not send password email. Backup aborted.');
            }

            // 5. STREAM THE NEW FILE AND DELETE IT
            Log::info('Backup: Download started', [
                'filename' => $request->filename,
                'secured_path' => $newSecuredPath,
                'file_size_mb' => round(filesize($newSecuredPath) / 1024 / 1024, 2),
                'user_id' => $admin->id,
                'timestamp' => now(),
            ]);

            return response()->download($newSecuredPath, $request->filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Backup: Download/encryption failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
                'error_code' => $e->getCode(),
                'filename' => $request->filename,
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            // Cleanup any temp files if they exist
            if ($newSecuredPath && file_exists($newSecuredPath)) {
                @unlink($newSecuredPath);

                Log::debug('Backup: Cleaned up secured zip after failure', [
                    'path' => $newSecuredPath,
                    'user_id' => Auth::guard('admin')->id(),
                ]);
            }
            if ($tempExtractDir && is_dir($tempExtractDir)) {
                $this->deleteDirectory($tempExtractDir);

                Log::debug('Backup: Cleaned up extraction directory after failure', [
                    'dir' => $tempExtractDir,
                    'user_id' => Auth::guard('admin')->id(),
                ]);
            }

            return back()->with('toast-error', 'Backup download failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request)
    {
        Log::info('Backup: Delete backup initiated', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'user_email' => Auth::guard('admin')->user()->email,
            'filename' => $request->input('filename'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            '_token'   => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);

        if ($validator->fails()) {
            Log::warning('Backup: Delete validation failed', [
                'error_message' => $validator->errors()->first(),
                'filename' => $request->input('filename'),
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return back()->with('toast-error', 'Invalid request!');
        }

        try {
            $filePath = storage_path('app/backups/BPS Library Management System/' . $request->filename);

            if (!file_exists($filePath)) {
                Log::error('Backup: File not found for deletion', [
                    'filename' => $request->filename,
                    'path' => $filePath,
                    'user_id' => Auth::guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                return back()->with('toast-error', 'Backup file not found!');
            }

            $fileSize = round(filesize($filePath) / 1024 / 1024, 2);
            unlink($filePath);

            Log::info('Backup: File deleted successfully', [
                'filename' => $request->filename,
                'file_size_mb' => $fileSize,
                'path' => $filePath,
                'deleted_by' => Auth::guard('admin')->id(),
                'deleted_by_name' => Auth::guard('admin')->user()->full_name,
                'timestamp' => now(),
            ]);

            return back()->with('toast-success', 'Backup deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Backup: Failed to delete backup', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'filename' => $request->filename,
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return back()->with('toast-error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    /**
     * Recursively delete a directory.
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;

        Log::debug('Backup: Deleting directory recursively', [
            'directory' => $dir,
            'user_id' => Auth::guard('admin')->id(),
        ]);

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            (is_dir($path)) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);

        Log::debug('Backup: Directory deleted successfully', [
            'directory' => $dir,
            'user_id' => Auth::guard('admin')->id(),
        ]);
    }
}