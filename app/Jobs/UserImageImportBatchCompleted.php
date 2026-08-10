<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;

class UserImageImportBatchCompleted
{
    public function __construct(public int $progressId)
    {
    }

    public function __invoke(Batch $batch)
    {
        $progressRecord = ImportProgress::find($this->progressId);
        if ($progressRecord && $progressRecord->isActive()) {
            $progressRecord->update(['status' => 'completed']);
        }
        Log::info('User Image Import Batch: Completed', ['progress_id' => $this->progressId]);
    }
}
