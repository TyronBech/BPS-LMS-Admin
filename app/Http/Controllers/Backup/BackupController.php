<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $files = glob(storage_path('app/backups/BPS Library Management System/*.zip'));

        // Convert them into a collection with filename & created date
        $backups = collect($files)->map(function ($file) {
            return [
                'filename' => basename($file),
                'type'     => 'Database',
                'size'     => round(filesize($file) / 1024 / 1024, 2) . ' MB',
                'created'  => date('Y-m-d H:i:s', filemtime($file)),
            ];
        })->sortByDesc('created');
        return view('backup.index', compact('backups'));
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_token' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast-error', 'Invalid request!');
        }

        try {
            Log::info("Starting database backup...");
            Artisan::call('backup:run --only-db');
            $output = Artisan::output();

            if (str_contains($output, 'Backup failed')) {
                throw new Exception($output);
            }

            // Find the latest backup ZIP file
            $backupPath = storage_path('app/backups/Laravel');
            $files = glob("$backupPath/*.zip");
            $latestFile = collect($files)->sortByDesc('filemtime')->first();

            if ($latestFile) {
                $password = env('BACKUP_ZIP_PASSWORD', 'MyStrongPassword123!');
                $newFile = str_replace('.zip', '_secured.zip', $latestFile);

                $zip = new ZipArchive();
                if ($zip->open($newFile, ZipArchive::CREATE) === TRUE) {

                    // Extract old zip contents temporarily
                    $tempDir = storage_path('app/backups/tmp_extract');
                    if (!file_exists($tempDir)) mkdir($tempDir, 0775, true);

                    $oldZip = new ZipArchive();
                    if ($oldZip->open($latestFile) === TRUE) {
                        $oldZip->extractTo($tempDir);
                        $oldZip->close();

                        // Add files to new zip with password
                        $iterator = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($tempDir),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );

                        foreach ($iterator as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($tempDir) + 1);
                                $zip->addFile($filePath, $relativePath);
                                $zip->setEncryptionName($relativePath, ZipArchive::EM_AES_256, $password);
                            }
                        }
                    }

                    $zip->close();

                    // Cleanup temp and delete old unencrypted backup
                    $this->deleteDirectory($tempDir);
                    unlink($latestFile);

                    Log::info("Backup secured with password (AES-256)!");
                }
            }
        } catch (Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            return back()->with('toast-error', 'Backup failed!');
        }

        Log::info("Database backup completed!");
        return back()->with('toast-success', 'Database backup created and secured successfully!');
    }
    /**
     * Create a fresh database backup and download it.
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_token' => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);
        if ($validator->fails()) {
            return back()->with('toast-error', 'Invalid request!');
        }
        try {
            $filePath = storage_path('app/backups/Laravel/' . $request->filename);
            if (!file_exists($filePath)) {
                return back()->with('toast-error', 'Backup file not found!');
            }
            return response()->download($filePath, $request->filename);
        } catch (\Exception $e) {
            return back()->with('toast-error', 'Backup download failed: ' . $e->getMessage());
        }
    }
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_token'  => 'required',
            'filename' => 'required|regex:/^[a-zA-Z0-9._-]+$/',
        ]);

        if ($validator->fails()) {
            return back()->with('toast-error', 'Invalid request!');
        }
        try {
            $filePath = storage_path('app/backups/Laravel/' . $request->filename);

            if (!file_exists($filePath)) {
                return back()->with('toast-error', 'Backup file not found!');
            }

            unlink($filePath);

            return back()->with('toast-success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('toast-error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }
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
