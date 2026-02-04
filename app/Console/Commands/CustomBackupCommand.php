<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
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
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");

            $dbName = $dbConfig['database'];
            $dbUser = $dbConfig['username'];
            $dbPass = $dbConfig['password'];
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'] ?? 3306;

            $dumpPath = $dbDir . '/' . $dbName . '_' . date('Y-m-d_His') . '.sql';

            // Create a temporary MySQL config file for credentials (more secure)
            $mysqlConfigPath = $tempDir . '/.my.cnf';
            $mysqlConfig = "[client]\n";
            $mysqlConfig .= "user={$dbUser}\n";
            $mysqlConfig .= "password=\"{$dbPass}\"\n";
            $mysqlConfig .= "host={$dbHost}\n";
            $mysqlConfig .= "port={$dbPort}\n";
            File::put($mysqlConfigPath, $mysqlConfig);
            chmod($mysqlConfigPath, 0600);

            // Get the dump binary path from config or use default
            $dumpBinary = config('backup.backup.dump.mysql.dump_binary_path');

            // If binary path is a directory, append mysqldump to it
            if ($dumpBinary && is_dir($dumpBinary)) {
                $dumpBinary = rtrim($dumpBinary, '/\\') . '/mysqldump';
            }

            // Default to mysqldump if not set
            if (!$dumpBinary) {
                $dumpBinary = 'mysqldump';
            }

            // Build the command using the config file
            $command = sprintf(
                '%s --defaults-extra-file=%s --single-transaction --quick --lock-tables=false %s',
                escapeshellarg($dumpBinary),
                escapeshellarg($mysqlConfigPath),
                escapeshellarg($dbName)
            );

            // Execute the command
            $result = Process::run($command);

            // Clean up the config file
            if (File::exists($mysqlConfigPath)) {
                File::delete($mysqlConfigPath);
            }

            if ($result->failed()) {
                $errorMsg = 'Database backup failed';
                if ($result->errorOutput()) {
                    $errorMsg .= ': ' . $result->errorOutput();
                }

                throw new \Exception($errorMsg);
            }

            // Save the output to file
            File::put($dumpPath, $result->output());

            if (!file_exists($dumpPath) || filesize($dumpPath) === 0) {
                throw new \Exception('Database backup failed: dump file is empty or was not created');
            }

            $this->info('Database backup completed: ' . basename($dumpPath) . ' (' . round(filesize($dumpPath) / 1024 / 1024, 2) . ' MB)');

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
