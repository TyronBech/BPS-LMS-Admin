<?php

namespace App\Jobs;

use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUserImageImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum seconds allowed. */
    public int $timeout = 1800;

    /** @var int Do not auto-retry on failure. */
    public int $tries = 1;

    /** Number of images per processing chunk. */
    private const CHUNK_SIZE = 10;

    /** Maximum file size in bytes (5 MB). */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * @param array<int, array{path: string, filename: string, id: string}> $matchedFiles  List of matched image file info.
     * @param int                                                            $progressId    ID of the ImportProgress record.
     * @param int                                                            $initiatedBy   Admin user ID who triggered the import.
     */
    public function __construct(
        public readonly array $matchedFiles,
        public readonly int $progressId,
        public readonly int $initiatedBy,
    ) {}

    /**
     * Execute the import job.
     *
     * @return void
     */
    public function handle(): void
    {
        ini_set('memory_limit', '512M');

        // Prevent Laravel from keeping all executed queries in memory
        DB::disableQueryLog();

        /** @var ImportProgress $progress */
        $progress = ImportProgress::findOrFail($this->progressId);
        $progress->update(['status' => 'processing']);

        Log::info('ProcessUserImageImport: Job started', [
            'progress_id'  => $this->progressId,
            'total_files'  => count($this->matchedFiles),
            'initiated_by' => $this->initiatedBy,
        ]);

        try {
            $processedRows = 0;
            $updatedCount  = 0;
            $skippedCount  = 0;
            $chunks        = array_chunk($this->matchedFiles, self::CHUNK_SIZE);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();

                foreach ($chunk as $fileInfo) {
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

                $progress->update([
                    'processed_rows' => $processedRows,
                    'updated_count'  => $updatedCount,
                    'new_count'      => $skippedCount,
                ]);

                // Free chunk data and collect garbage to release memory
                unset($chunk);
                gc_collect_cycles();

                Log::debug('ProcessUserImageImport: Chunk committed', [
                    'progress_id'    => $this->progressId,
                    'chunk_index'    => $chunkIndex,
                    'processed_rows' => $processedRows,
                ]);
            }

            $progress->update([
                'status'         => 'completed',
                'processed_rows' => count($this->matchedFiles),
                'updated_count'  => $updatedCount,
                'new_count'      => $skippedCount,
            ]);

            Log::info('ProcessUserImageImport: Completed successfully', [
                'progress_id'   => $this->progressId,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $progress->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('ProcessUserImageImport: Job failed', [
                'progress_id'   => $this->progressId,
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up all matched files that were passed to this job, just to be sure
            foreach ($this->matchedFiles as $fileInfo) {
                $filePath = $fileInfo['path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Check if there are other active imports before deleting the directory
            $activeImport = ImportProgress::where('type', 'user_images')
                ->where('id', '!=', $this->progressId)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();

            if (!$activeImport && Storage::exists('temp_user_images')) {
                Storage::deleteDirectory('temp_user_images');
            }
        }
    }

    /**
     * Find a user by student ID number or employee ID.
     *
     * Checks students first (school convention: student and employee IDs have
     * different structures, so collisions are unlikely but students take priority).
     *
     * @param string $idValue The ID to search for.
     * @return User|null
     */
    private function findUserByIdValue(string $idValue): ?User
    {
        // Try student first
        $studentDetail = StudentDetail::where('id_number', $idValue)->first();
        if ($studentDetail) {
            return User::find($studentDetail->user_id);
        }

        // Then try employee
        $employeeDetail = EmployeeDetail::where('employee_id', $idValue)->first();
        if ($employeeDetail) {
            return User::find($employeeDetail->user_id);
        }

        return null;
    }
}
