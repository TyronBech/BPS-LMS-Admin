<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Log;

class FetchDataController extends Controller
{
    public function fetchCurrentTimeInUsers(){
        $today = Carbon::today();

        $activeCount = Log::whereDate('timestamp', $today)
            ->where('action', 'Time in')
            ->whereNotIn('user_id', function($query) use ($today) {
                $query->select('user_id')
                      ->from('log_user_logs')
                      ->whereDate('timestamp', $today)
                      ->where('action', 'Time out');
            })
            ->count();
        return response()->json(['active_count' => $activeCount]);
    }
}
