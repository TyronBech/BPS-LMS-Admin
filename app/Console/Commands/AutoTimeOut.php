<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

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
        try{
            DB::statement('CALL AutoTimeoutUsers()');
        } catch (Exception $e) {
            Log::error('Error in AutoTimeOut command: ' . $e->getMessage());
            return;
        }
        Log::info('Auto time out command executed.');
    }
}
