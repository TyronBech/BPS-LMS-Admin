<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:db-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database and logs backup automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database and logs backup...');
        Log::info('Automated backup started (DB + Logs).');

        try {
            Artisan::call('backup:custom');
            $output = Artisan::output();

            Log::info('Automated backup completed successfully.', [
                'output' => $output,
                'timestamp' => now(),
            ]);

            $this->info('Database and logs backup completed successfully.');
            return 0;
        } catch (\Exception $e) {
            Log::error('Automated backup failed.', [
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
