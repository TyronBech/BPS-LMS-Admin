<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoTimeOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-time-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command automatically times out users after school hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Auto time out command executed.');
    }
}
