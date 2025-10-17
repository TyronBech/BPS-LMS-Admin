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
    public function fetchCurrentTimeInUsers()
    {
        $today = Carbon::today();

        $activeCount = Log::where('time_in', '!=', null)
            ->where('time_out', null)
            ->whereDate('time_in', $today)
            ->count();
        return response()->json(['active_count' => $activeCount]);
    }
    public function timeoutAllUsers()
    {
        try {
            DB::statement("SET time_zone = '+08:00'");
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
    public function fetchMonthlyUsers()
    {
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
    public function totalBooks()
    {
        $totalBooks = Book::count();
        return response()->json(['total_books' => $totalBooks]);
    }
    public function fetchTransactionHistory()
    {
        // 1) Define the last 12 months range (from oldest to newest)
        $now = Carbon::now();
        $start = $now->copy()->subMonths(11)->startOfMonth();

        // Build a list of months for the last 12 months
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $dt = $start->copy()->addMonths($i);
            $months->push([
                'key'   => $dt->format('Y-m'),   // e.g. "2025-08" used for lookup
                'label' => $dt->format('Y F'),   // e.g. "2025 August" for chart
            ]);
        }

        // 2) Aggregate all transaction types in a single query
        $records = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
            DB::raw("DATE_FORMAT(created_at, '%Y %M') as month_label"),
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Borrowed' THEN 1 ELSE 0 END) as borrowed"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Returned' THEN 1 ELSE 0 END) as returned"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Reserved' THEN 1 ELSE 0 END) as reserved")
        )
            ->whereBetween('created_at', [$start, $now->copy()->endOfMonth()])
            ->groupBy('ym', 'month_label')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // 3) Build aligned arrays for each dataset
        $labels = [];
        $total = [];
        $borrowed = [];
        $returned = [];
        $reserved = [];

        foreach ($months as $m) {
            $labels[] = $m['label'];

            if (isset($records[$m['key']])) {
                $r = $records[$m['key']];
                $total[]    = (int) $r->total;
                $borrowed[] = (int) $r->borrowed;
                $returned[] = (int) $r->returned;
                $reserved[] = (int) $r->reserved;
            } else {
                $total[] = $borrowed[] = $returned[] = $reserved[] = 0;
            }
        }

        // 4) Remove preceding months that have all zeros
        $firstNonZeroIndex = null;
        foreach ($total as $i => $value) {
            if ($value > 0 || $borrowed[$i] > 0 || $returned[$i] > 0 || $reserved[$i] > 0) {
                $firstNonZeroIndex = $i;
                break;
            }
        }

        if (!is_null($firstNonZeroIndex)) {
            $labels   = array_slice($labels, $firstNonZeroIndex);
            $total    = array_slice($total, $firstNonZeroIndex);
            $borrowed = array_slice($borrowed, $firstNonZeroIndex);
            $returned = array_slice($returned, $firstNonZeroIndex);
            $reserved = array_slice($reserved, $firstNonZeroIndex);
        }

        // 5) Return structured JSON for Chart.js
        return response()->json([
            'labels'      => $labels,
            'total'       => $total,
            'borrowed'    => $borrowed,
            'returned'    => $returned,
            'reserved'    => $reserved,
        ]);
    }
    public function fetchYearlyAquiredBooks()
    {
        $yearlyRecord = Book::select(
            DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"))
            ->orderBy(DB::raw("MIN(created_at)")) // optional: to order correctly from oldest to newest
            ->get();
        return response()->json($yearlyRecord);
    }
    public function fetchRegisteredUsers()
    {
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
    public function mostVisitedStudents()
    {
        try {
            $levels = range(7, 12);
            $results = collect();

            foreach ($levels as $level) {
                $topStudents = User::whereHas('students', function ($q) use ($level) {
                    $q->where('level', $level);
                })
                    ->with('students')
                    ->withCount(['logs as logs_count' => function ($query) {
                        $query->whereNotNull('time_in')
                            ->whereYear('time_in', Carbon::now()->year)
                            ->select(DB::raw('COUNT(DISTINCT DATE(time_in))'));
                    }])
                    ->orderByDesc('logs_count')
                    ->take(6)
                    ->get();

                $results->push([
                    'level' => $level,
                    'students' => $topStudents,
                ]);
            }
            return response()->json($results->values(), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    public function mostBorrowedStudents()
    {
        try {
            $levels = range(7, 12);
            $results = collect();

            foreach ($levels as $level) {
                $topStudents = User::whereHas('students', function ($q) use ($level) {
                    $q->where('level', $level);
                })
                    ->with('students')
                    ->withCount(['transactions as borrow_count' => function ($query) {
                        $query->whereYear('created_at', Carbon::now()->year);
                    }])
                    ->orderByDesc('borrow_count')
                    ->take(3)
                    ->get();

                $results->push([
                    'level' => $level,
                    'students' => $topStudents,
                ]);
            }

            return response()->json($results->values(), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    public function topBooksBorrowed()
    {
        try {
            $topBooks = Book::with(['transactions' => function ($query) {
                $query->whereIn('transaction_type', ['Borrowed', 'Returned']);
            }])
                ->get()
                ->groupBy('title')
                ->map(function ($groupedBooks) {
                    return [
                        'title' => $groupedBooks->first()->title,
                        'total_borrows' => $groupedBooks->sum(fn($book) => $book->transactions->count()),
                    ];
                })
                ->sortByDesc('total_borrows')
                ->take(5)
                ->values();

            return response()->json([
                'labels' => $topBooks->pluck('title'),
                'counts' => $topBooks->pluck('total_borrows'),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    public function topCategoriesBorrowed()
    {
        try {
            $topCategories = Book::with(['transactions' => function ($query) {
                $query->whereIn('transaction_type', ['Borrowed', 'Returned']);
            }])
                ->get()
                ->groupBy('category_id')
                ->map(function ($groupedCategories) {
                    return [
                        'category' => $groupedCategories->first()->category->name,
                        'total_borrows' => $groupedCategories->sum(fn($book) => $book->transactions->count()),
                    ];
                })
                ->sortByDesc('total_borrows')
                ->take(5)
                ->values();

            return response()->json([
                'labels' => $topCategories->pluck('category'),
                'counts' => $topCategories->pluck('total_borrows'),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
