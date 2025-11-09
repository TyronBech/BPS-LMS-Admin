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
    public function fetchMonthlyUsers(Request $request)
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
    public function fetchTransactionHistory(Request $request)
    {
        $now = Carbon::now();
        $startDT = $now->copy()->subMonths(11)->startOfMonth();
        $endDT = $now->copy()->endOfMonth();

        // Build months list between startDT and endDT (inclusive)
        $months = collect();
        $cursor = $startDT->copy()->startOfMonth();
        while ($cursor->lte($endDT)) {
            $months->push([
                'key' => $cursor->format('Y-m'),
                'label' => $cursor->format('Y F'),
            ]);
            $cursor->addMonth();
        }

        $records = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
            DB::raw("DATE_FORMAT(created_at, '%Y %M') as month_label"),
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Borrowed' THEN 1 ELSE 0 END) as borrowed"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Returned' THEN 1 ELSE 0 END) as returned"),
            DB::raw("SUM(CASE WHEN transaction_type = 'Reserved' THEN 1 ELSE 0 END) as reserved")
        )
            ->whereBetween('created_at', [$startDT, $endDT])
            ->groupBy('ym', 'month_label')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

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

        // Trim leading all-zero months
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
    public function mostVisitedStudents(Request $request)
    {
        try {
            $levels = range(7, 12);
            $results = collect();

            $start = $request->query('start');
            $end = $request->query('end');
            $hasRange = false;
            $startDT = null;
            $endDT = null;
            if ($start && $end) {
                try {
                    $startDT = Carbon::parse($start)->startOfDay();
                    $endDT = Carbon::parse($end)->endOfDay();
                    $hasRange = true;
                } catch (\Throwable $e) {
                    $hasRange = false;
                }
            }

            foreach ($levels as $level) {
                $topStudents = User::whereHas('students', function ($q) use ($level) {
                    $q->where('level', $level);
                })
                    ->with('students')
                    ->withCount(['logs as logs_count' => function ($query) use ($hasRange, $startDT, $endDT) {
                        $query->whereNotNull('time_in');
                        if ($hasRange) {
                            $query->whereBetween('time_in', [$startDT, $endDT]);
                        } else {
                            $query->whereYear('time_in', Carbon::now()->year);
                        }
                        $query->select(DB::raw('COUNT(DISTINCT DATE(time_in))'));
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
    public function mostBorrowedStudents(Request $request)
    {
        try {
            $levels = range(7, 12);
            $results = collect();

            $start = $request->query('start');
            $end = $request->query('end');
            $hasRange = false;
            $startDT = null;
            $endDT = null;
            if ($start && $end) {
                try {
                    $startDT = Carbon::parse($start)->startOfDay();
                    $endDT = Carbon::parse($end)->endOfDay();
                    $hasRange = true;
                } catch (\Throwable $e) {
                    $hasRange = false;
                }
            }

            foreach ($levels as $level) {
                $topStudents = User::whereHas('students', function ($q) use ($level) {
                    $q->where('level', $level);
                })
                    ->with('students')
                    ->withCount(['transactions as borrow_count' => function ($query) use ($hasRange, $startDT, $endDT) {
                        if ($hasRange) {
                            $query->whereBetween('created_at', [$startDT, $endDT]);
                        } else {
                            $query->whereYear('created_at', Carbon::now()->year);
                        }
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
    public function topBooksBorrowed(Request $request)
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
    public function topCategoriesBorrowed(Request $request)
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
