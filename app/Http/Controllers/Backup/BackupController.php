<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BackupController extends Controller
{
    public function index()
    {
        $files = glob(storage_path('app/backups/Laravel/*.zip'));

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
            // Run backup
            Log::info("Starting database backup...");
            Artisan::call('backup:run --only-db');
            $output = Artisan::output();
            if (str_contains($output, 'Backup failed')) {
                throw new Exception($output);
            }
        } catch (Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            return back()->with('toast-error', 'Backup failed!');
        }
        Log::info("Database backup completed!");
        return back()->with('toast-success', 'Database backup created successfully!');
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
}
