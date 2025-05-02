<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Transaction;

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
    public function fetchMonthlyUsers(){
        $monthlyRecord = Log::select(
            DB::raw("DATE_FORMAT(timestamp, '%Y %M') as month"),
            DB::raw('COUNT(*) as count')
        )
        ->where('action', 'Time in')
        ->groupBy(DB::raw("DATE_FORMAT(timestamp, '%Y %M')"))
        ->orderBy(DB::raw("MIN(timestamp)")) // optional: to order correctly from oldest to newest
        ->limit(12)
        ->get();
        return response()->json($monthlyRecord);
    }
    public function totalBooks(){
        $totalBooks = Book::count();
        return response()->json(['total_books' => $totalBooks]);
    }
    public function fetchTransactionHistory(){
        $monthlyRecord = Transaction::select(
            DB::raw("DATE_FORMAT(date_borrowed, '%Y %M') as month"),
            DB::raw('COUNT(*) as count')
        )->
        groupBy(DB::raw("DATE_FORMAT(date_borrowed, '%Y %M')"))
        ->orderBy(DB::raw("MIN(date_borrowed)")) // optional: to order correctly from oldest to newest
        ->limit(12)
        ->get();
        $borrowed = Transaction::select(
            DB::raw('COUNT(*) as count')
        )
        ->where('transaction_type', 'Borrowed')
        ->groupBy(DB::raw("DATE_FORMAT(date_borrowed, '%Y %M')"))
        ->orderBy(DB::raw("MIN(date_borrowed)")) // optional: to order correctly from oldest to newest
        ->limit(12)
        ->get();
        $returned = Transaction::select(
            DB::raw('COUNT(*) as count')
        )
        ->where('transaction_type', 'Returned')
        ->groupBy(DB::raw("DATE_FORMAT(date_borrowed, '%Y %M')"))
        ->orderBy(DB::raw("MIN(date_borrowed)")) // optional: to order correctly from oldest to newest
        ->limit(12)
        ->get();
        return response()->json([
            'transaction_history' => $monthlyRecord,
            'borrowed' => $borrowed,
            'returned' => $returned
        ]);
    }
}
