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

            $dumpSuccess = false;
            $errorMessage = '';

            // Try mysqldump first (if shell functions are available)
            if (function_exists('proc_open') && function_exists('escapeshellarg')) {
                try {
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

                    if ($result->successful() && strlen($result->output()) > 0) {
                        File::put($dumpPath, $result->output());
                        $dumpSuccess = true;
                        $this->info('Database backup completed using mysqldump.');
                    } else {
                        $errorMessage = $result->errorOutput() ?: 'Unknown mysqldump error (exit code: ' . $result->exitCode() . ')';
                        $this->warn('mysqldump failed: ' . $errorMessage);
                    }
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->warn('Exception during mysqldump execution: ' . $errorMessage);
                }
            } else {
                $this->info('Shell execution functions (proc_open) are disabled.');
            }

            // Fallback to pure PHP database dumper if mysqldump failed or is disabled
            if (!$dumpSuccess) {
                $this->info('Falling back to pure PHP database dumper...');
                try {
                    $this->dumpDatabasePhpFallback($connection, $dumpPath);
                    $dumpSuccess = true;
                    $this->info('Database backup completed successfully using PHP fallback.');
                } catch (\Exception $e) {
                    $this->error('PHP database dump failed: ' . $e->getMessage());
                    throw new \Exception('Database backup failed completely. (mysqldump error: ' . $errorMessage . ' | PHP fallback error: ' . $e->getMessage() . ')');
                }
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
            $backupDir = storage_path('app/backups');

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

    /**
     * Fallback database dumper implemented in pure PHP/PDO.
     * Generates a schema + data dump without relying on shell commands or external binaries.
     */
    private function dumpDatabasePhpFallback(string $connectionName, string $dumpPath): void
    {
        $pdo = \Illuminate\Support\Facades\DB::connection($connectionName)->getPdo();

        $sql = "-- Database Backup Fallback (Pure PHP)\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        File::put($dumpPath, $sql);

        // Get all tables
        $tablesResult = $pdo->query('SHOW TABLES');
        $tables = $tablesResult->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Get create table statement
            $createTableResult = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $createTableResult->fetch(\PDO::FETCH_NUM);
            
            $tableSql = "\n\n-- Table structure for table `{$table}`\n";
            $tableSql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $tableSql .= $createTable[1] . ";\n\n";
            File::append($dumpPath, $tableSql);

            // Fetch table rows
            $rowsResult = $pdo->query("SELECT * FROM `{$table}`");
            $columnCount = $rowsResult->columnCount();

            $insertSql = "";
            $rowCount = 0;
            while ($row = $rowsResult->fetch(\PDO::FETCH_NUM)) {
                if ($rowCount % 100 === 0) {
                    if ($insertSql !== "") {
                        File::append($dumpPath, $insertSql . ";\n");
                    }
                    $insertSql = "INSERT INTO `{$table}` VALUES ";
                } else {
                    $insertSql .= ",\n";
                }

                $values = [];
                for ($i = 0; $i < $columnCount; $i++) {
                    if (is_null($row[$i])) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($row[$i])) {
                        $values[] = $row[$i];
                    } else {
                        $values[] = $pdo->quote($row[$i]);
                    }
                }
                $insertSql .= "(" . implode(', ', $values) . ")";
                $rowCount++;
            }

            if ($rowCount > 0 && $insertSql !== "") {
                File::append($dumpPath, $insertSql . ";\n");
            }
        }

        File::append($dumpPath, "\nSET FOREIGN_KEY_CHECKS=1;\n");
    }
}
