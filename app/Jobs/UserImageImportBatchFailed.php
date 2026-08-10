<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;

class UserImageImportBatchFailed
{
    public function __construct(public int $progressId)
    {
    }

    public function __invoke(Batch $batch, \Throwable $e)
    {
        $progressRecord = ImportProgress::find($this->progressId);
        if ($progressRecord && $progressRecord->isActive()) {
            $progressRecord->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
        Log::error('User Image Import Batch: Failed', [
            'progress_id'   => $this->progressId,
            'error_message' => $e->getMessage(),
        ]);
    }
}
