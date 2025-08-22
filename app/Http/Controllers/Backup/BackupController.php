<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Backup\BackupDestination;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup.index');
    }
    public function create(Request $request)
    {
        try {
            // Run backup
            Artisan::queue('backup:run --only-db');
        } catch (Exception $e) {
            return back()->with('toast-error', $e->getMessage());
        }
        return back()->with('toast-success', 'Database backup created successfully!');
    }

    /**
     * Create a fresh database backup and download it.
     */
    public function download(Request $request)
    {
        try {
            // Get all backup zip files
            $files = glob(storage_path('app/backups/Laravel/*.zip'));

            if (empty($files)) {
                return back()->with('toast-error', 'No backup file found!');
            }

            // Sort by modification time descending and pick the first (latest)
            $latest = collect($files)
                ->sortByDesc(fn($file) => filemtime($file))
                ->first();

            // Download the latest backup
            return response()->download($latest, 'database-backup.zip');
        } catch (\Exception $e) {
            return back()->with('toast-error', 'Backup failed: ' . $e->getMessage());
        }
    }
    // Todo: Implement restore functionality
    public function restore(Request $request)
    {
        try {
            // Path to the latest backup (same logic as download)
            $files = glob(storage_path('app/backups/Laravel/*.zip'));

            if (empty($files)) {
                return back()->with('toast-error', 'No backup file found!');
            }

            $latest = collect($files)
                ->sortByDesc(fn($file) => filemtime($file))
                ->first();

            // If it's a zip, extract it first
            $zip = new ZipArchive;
            if ($zip->open($latest) === TRUE) {
                $zip->extractTo(storage_path('app/backups/Laravel/temp'));
                $zip->close();

                // Assume the zip contains a single SQL file
                $sqlFiles = glob(storage_path('app/backups/Laravel/temp/*.sql'));
                $sqlFile = $sqlFiles[0] ?? null;

                if (!$sqlFile) {
                    return back()->with('toast-error', 'No SQL file found in the backup!');
                }
            } else {
                return back()->with('toast-error', 'Failed to open zip backup.');
            }

            // Database credentials from .env
            $dbHost = env('DB_HOST');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // Execute MySQL restore command
            $command = "mysql -h $dbHost -u $dbUser -p$dbPass $dbName < $sqlFile";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return back()->with('toast-error', 'Database restore failed!');
            }
        } catch (Exception $e) {
            return back()->with('toast-error', 'Restore failed: ' . $e->getMessage());
        }
        return back()->with('toast-success', 'Database restored successfully!');
    }
}
