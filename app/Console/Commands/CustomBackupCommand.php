<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CustomBackupCommand extends Command
{
    protected $signature = 'backup:custom';
    protected $description = 'Create a custom backup with database and logs in separate folders';

    public function handle()
    {
        $this->info('Starting custom backup...');

        try {
            // Create temporary directories
            $tempDir = storage_path('app/backup-temp/' . Str::random(10));
            $dbDir = $tempDir . '/db';
            $logsDir = $tempDir . '/logs';

            File::makeDirectory($dbDir, 0755, true);
            File::makeDirectory($logsDir, 0755, true);

            $this->info('Created temporary directories');

            // 1. Backup Database
            $this->info('Backing up database...');
            $dbName = config('database.connections.' . config('database.default') . '.database');
            $dbUser = config('database.connections.' . config('database.default') . '.username');
            $dbPass = config('database.connections.' . config('database.default') . '.password');
            $dbHost = config('database.connections.' . config('database.default') . '.host');
            $dbPort = config('database.connections.' . config('database.default') . '.port', 3306);
            
            $dumpPath = $dbDir . '/' . $dbName . '_' . date('Y-m-d_His') . '.sql';
            
            // Get the dump binary path from config or use default
            $dumpBinary = config('backup.backup.dump.mysql.dump_binary_path', 'mysqldump');
            
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s --port=%d %s > %s 2>&1',
                escapeshellarg($dumpBinary),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                $dbPort,
                escapeshellarg($dbName),
                escapeshellarg($dumpPath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($dumpPath)) {
                throw new \Exception('Database backup failed: ' . implode("\n", $output));
            }

            $this->info('Database backup completed: ' . basename($dumpPath));

            // 2. Copy Logs
            $this->info('Copying logs...');
            $logsPath = storage_path('logs');
            
            if (File::exists($logsPath)) {
                $logFiles = File::allFiles($logsPath);
                $copiedCount = 0;
                
                foreach ($logFiles as $file) {
                    $relativePath = str_replace($logsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $destinationPath = $logsDir . DIRECTORY_SEPARATOR . $relativePath;
                    
                    // Create subdirectories if needed
                    $destinationDir = dirname($destinationPath);
                    if (!File::exists($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                    }
                    
                    File::copy($file->getPathname(), $destinationPath);
                    $copiedCount++;
                }
                
                $this->info("Copied {$copiedCount} log file(s)");
            } else {
                $this->warn('Logs directory not found');
            }

            // 3. Create ZIP archive
            $this->info('Creating ZIP archive...');
            $backupDir = storage_path('app/backups/BPS Library Management System');
            
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $zipFileName = date('Y-m-d_His') . '.zip';
            $zipPath = $backupDir . '/' . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Could not create ZIP file');
            }

            // Add database files
            $this->addDirectoryToZip($zip, $dbDir, 'db');
            
            // Add log files
            $this->addDirectoryToZip($zip, $logsDir, 'logs');

            $zip->close();

            $this->info('ZIP archive created: ' . $zipFileName);

            // 4. Cleanup temporary directory
            File::deleteDirectory($tempDir);
            $this->info('Cleaned up temporary files');

            $this->info('Backup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            
            // Cleanup on failure
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            throw $e;
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, $directory, $zipPath)
    {
        if (!File::exists($directory)) {
            return;
        }

        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $zipPath . '/' . $relativePath);
        }
    }
}
