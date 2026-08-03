<?php

namespace App\Jobs;

use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessUserImageImport implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum seconds allowed. */
    public int $timeout = 1800;

    /** @var int Do not auto-retry on failure. */
    public int $tries = 1;

    /** Maximum file size in bytes (5 MB). */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * @param array<int, array{path: string, filename: string, id: string}> $matchedFiles  List of matched image file info.
     * @param int $progressId ID of the ImportProgress record.
     * @param int $initiatedBy Admin user ID who triggered the import.
     */
    public function __construct(
        public readonly array $matchedFiles,
        public readonly int $progressId,
        public readonly int $initiatedBy,
    ) {}

    /**
     * Called by the queue worker when the job is permanently failed.
     *
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        $progress = ImportProgress::find($this->progressId);
        if ($progress && $progress->isActive()) {
            $progress->update([
                'status'        => 'failed',
                'error_message' => $progress->error_message ?: $exception->getMessage(),
            ]);
        }

        Log::error('ProcessUserImageImport: Job permanently failed', [
            'progress_id'   => $this->progressId,
            'error_message' => $exception->getMessage(),
        ]);
    }

    /**
     * Execute the import job chunk.
     *
     * @return void
     */
    public function handle(): void
    {
        // Check if the batch or progress was cancelled before starting
        if ($this->batch()?->cancelled()) {
            return;
        }

        ini_set('memory_limit', '512M');

        // Prevent Laravel from keeping all executed queries in memory
        DB::disableQueryLog();

        /** @var ImportProgress|null $progress */
        $progress = ImportProgress::find($this->progressId);
        if (!$progress || $progress->status === 'cancelled') {
            return;
        }

        if ($progress->status === 'pending') {
            $progress->update(['status' => 'processing']);
        }

        Log::info('ProcessUserImageImport: Job chunk started', [
            'progress_id'  => $this->progressId,
            'total_files'  => count($this->matchedFiles),
            'initiated_by' => $this->initiatedBy,
        ]);

        try {
            $processedRows = 0;
            $updatedCount  = 0;
            $skippedCount  = 0;

            DB::beginTransaction();

            foreach ($this->matchedFiles as $fileInfo) {
                if ($this->batch()?->cancelled()) {
                    DB::rollBack();
                    return;
                }

                $filePath = $fileInfo['path'];
                $idValue  = $fileInfo['id'];

                // Verify file still exists
                if (!file_exists($filePath)) {
                    Log::warning('ProcessUserImageImport: File no longer exists, skipping', [
                        'path' => $filePath,
                    ]);
                    $skippedCount++;
                    $processedRows++;
                    continue;
                }

                // Re-validate file size
                if (filesize($filePath) > self::MAX_FILE_SIZE) {
                    Log::warning('ProcessUserImageImport: File exceeds 5MB limit, skipping', [
                        'path' => $filePath,
                        'size' => filesize($filePath),
                    ]);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    $skippedCount++;
                    $processedRows++;
                    continue;
                }

                // Look up user: try student first, then employee
                $user = $this->findUserByIdValue($idValue);

                if (!$user) {
                    Log::warning('ProcessUserImageImport: No user found for ID, skipping', [
                        'id_value' => $idValue,
                    ]);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    $skippedCount++;
                    $processedRows++;
                    continue;
                }

                // Read and base64-encode the image
                $imageContents = @file_get_contents($filePath);
                if ($imageContents === false) {
                    Log::warning('ProcessUserImageImport: Failed to read file, skipping', [
                        'path' => $filePath,
                    ]);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    $skippedCount++;
                    $processedRows++;
                    continue;
                }

                $base64Image = base64_encode($imageContents);

                // Free the raw image data from memory immediately
                unset($imageContents);

                $user->update(['profile_image' => $base64Image]);

                // Free the base64 string from memory
                unset($base64Image);

                // Delete the temp file to save disk space
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }

                $updatedCount++;
                $processedRows++;
            }

            DB::commit();

            // Atomically update progress counts in DB to safely support concurrent batch execution
            DB::statement(
                "UPDATE import_progress SET processed_rows = processed_rows + ?, updated_count = updated_count + ?, new_count = new_count + ? WHERE id = ?",
                [$processedRows, $updatedCount, $skippedCount, $this->progressId]
            );

            gc_collect_cycles();

            Log::debug('ProcessUserImageImport: Job chunk committed', [
                'progress_id'    => $this->progressId,
                'processed_rows' => $processedRows,
                'updated_count'  => $updatedCount,
                'skipped_count'  => $skippedCount,
            ]);

            // If running directly without Bus::batch (e.g. unit testing), finalize status when complete
            if ($this->batch() === null) {
                $progress->refresh();
                if ($progress->processed_rows >= $progress->total_rows && $progress->isActive()) {
                    $progress->update(['status' => 'completed']);
                }
            }

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $progress->refresh();
            if ($progress->status !== 'cancelled') {
                $progress->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('ProcessUserImageImport: Job chunk failed', [
                'progress_id'   => $this->progressId,
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Find a user by student ID number or employee ID.
     *
     * @param string $idValue The ID to search for.
     * @return User|null
     */
    private function findUserByIdValue(string $idValue): ?User
    {
        $studentDetail = StudentDetail::where('id_number', $idValue)->first();
        if ($studentDetail) {
            return User::find($studentDetail->user_id);
        }

        $employeeDetail = EmployeeDetail::where('employee_id', $idValue)->first();
        if ($employeeDetail) {
            return User::find($employeeDetail->user_id);
        }

        return null;
    }
}

