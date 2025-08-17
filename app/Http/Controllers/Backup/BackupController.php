<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Backup\BackupDestination;

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
            Artisan::queue('backup:run');
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
}
