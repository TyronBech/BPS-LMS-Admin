<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Storage;

class UserImageImportBatchFinished
{
    public function __construct(public int $progressId)
    {
    }

    public function __invoke(Batch $batch)
    {
        $activeImport = ImportProgress::where('type', 'user_images')
            ->where('id', '!=', $this->progressId)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if (!$activeImport && Storage::exists('temp_user_images')) {
            Storage::deleteDirectory('temp_user_images');
        }
    }
}
