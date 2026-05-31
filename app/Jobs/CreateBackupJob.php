<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\BackupSucceeded;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * The admin user ID who triggered the backup.
     *
     * @var int
     */
    protected $adminId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('BackupJob: Started', ['admin_id' => $this->adminId]);

        // Lock backup generation for 10 minutes
        Cache::put('backup_in_progress', true, 600);

        try {
            Artisan::call('backup:custom');
            $output = Artisan::output();

            Log::debug('BackupJob: Command output', ['output' => $output]);

            if (str_contains($output, 'Backup failed') || str_contains($output, 'failed')) {
                throw new Exception($output ?: 'Custom backup command failed.');
            }

            $admin = User::find($this->adminId);
            if ($admin) {
                Notification::send($admin, new BackupSucceeded($admin->first_name . ' ' . $admin->last_name));
            }

            Log::info('BackupJob: Completed successfully', ['admin_id' => $this->adminId]);
        } catch (Exception $e) {
            Log::error('BackupJob: Failed with exception', [
                'admin_id' => $this->adminId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            Cache::forget('backup_in_progress');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Cache::forget('backup_in_progress');
        Log::error('BackupJob: Job marked as failed', [
            'admin_id' => $this->adminId,
            'error' => $exception->getMessage()
        ]);
    }
}
