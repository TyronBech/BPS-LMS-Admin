<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Log as LogFacade;

class FetchDataController extends Controller
{
    public function fetchCurrentTimeInUsers(){
        $today = Carbon::today();

        $activeCount = Log::where('time_in' , '!=', null)
            ->where('time_out', null)
            ->whereDate('time_in', $today)
            ->count();
        return response()->json(['active_count' => $activeCount]);
    }
    public function timeoutAllUsers(){
        try {
            DB::statement('CALL AutoTimeoutUsers()');
            LogFacade::info('Auto time out command executed.');
        } catch (\Exception $e) {
            session()->flash('toast-error', 'An error occurred while executing the auto time out command.');
            LogFacade::error('Error executing auto time out command: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the request.'
            ], 500);
        }
        session()->flash('toast-success', 'All users have been timed out successfully.');
        return response()->json([
            'message' => 'All users have been timed out successfully.'
        ]);
    }
    public function fetchMonthlyUsers(){
        $monthlyRecord = Log::select(
            DB::raw("DATE_FORMAT(time_in, '%Y %M') as month"),
            DB::raw('COUNT(*) as count')
        )
        ->where('time_in', '!=', null)
        ->groupBy(DB::raw("DATE_FORMAT(time_in, '%Y %M')"))
        ->orderBy(DB::raw("MIN(time_in)")) // optional: to order correctly from oldest to newest
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
        $reserved = Transaction::select(
            DB::raw('COUNT(*) as count')
        )
        ->where('transaction_type', 'Reserved')
        ->groupBy(DB::raw("DATE_FORMAT(date_borrowed, '%Y %M')"))
        ->orderBy(DB::raw("MIN(date_borrowed)")) // optional: to order correctly from oldest to newest
        ->limit(12)
        ->get();
        return response()->json([
            'transaction_history' => $monthlyRecord,
            'borrowed' => $borrowed,
            'returned' => $returned,
            'reserved' => $reserved
        ]);
    }
    public function fetchYearlyAquiredBooks(){
        $yearlyRecord = Book::select(
            DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"))
        ->orderBy(DB::raw("MIN(created_at)")) // optional: to order correctly from oldest to newest
        ->get();
        return response()->json($yearlyRecord);
    }
    public function fetchRegisteredUsers(){
        $students = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'student');
        })->count();
        
        $employees = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'employee');
        })->count();
        $visitors = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'visitor');
        })->count();
        return response()->json([
            'students' => $students,
            'employees' => $employees,
            'visitors' => $visitors
        ]);
    }
}
