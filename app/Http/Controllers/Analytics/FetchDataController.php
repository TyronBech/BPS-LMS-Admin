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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LogFacade;

class FetchDataController extends Controller
{
    /**
     * Fetch the count of active users at the current time
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchCurrentTimeInUsers()
    {
        try {
            $today = Carbon::today();

            LogFacade::info('Analytics: Fetching current time in users', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->full_name ?? 'N/A',
                'date' => $today->toDateString(),
                'timestamp' => now(),
            ]);

            $activeCount = Log::where('time_in', '!=', null)
                ->where('time_out', null)
                ->whereDate('time_in', $today)
                ->count();

            LogFacade::info('Analytics: Current time in users fetched successfully', [
                'active_count' => $activeCount,
                'date' => $today->toDateString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json(['active_count' => $activeCount]);
        } catch (\Exception $e) {
            LogFacade::error('Analytics: Error fetching current time in users', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);
            return response()->json(['error' => 'Unable to fetch current user count'], 500);
        }
    }

    /**
     * Auto timeout all users.
     *
     * This function will timeout all users who are currently logged in.
     *
     * @return \Illuminate\Http\Response
     */
    public function timeoutAllUsers()
    {
        LogFacade::info('Analytics: Auto timeout all users initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        try {
            DB::statement("SET time_zone = '+08:00'");

            LogFacade::debug('Analytics: Executing auto timeout stored procedure', [
                'user_id' => Auth::id(),
            ]);

            DB::statement('CALL AutoTimeoutUsers()');

            LogFacade::info('Analytics: Auto timeout executed successfully', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->full_name,
                'timestamp' => now(),
            ]);

            session()->flash('toast-success', 'All users have been timed out successfully.');

            return response()->json([
                'message' => 'All users have been timed out successfully.'
            ]);

        } catch (\Exception $e) {
            LogFacade::error('Analytics: Error executing auto timeout command', [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);

            session()->flash('toast-error', 'An error occurred while executing the auto time out command.');

            return response()->json([
                'error' => 'An error occurred while processing the request.'
            ], 500);
        }
    }

    /**
     * Fetch the monthly count of users.
     *
     * This function fetches the count of users per month for the past 12 months.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMonthlyUsers(Request $request)
    {
        try {
            LogFacade::info('Analytics: Fetching monthly users data', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->full_name ?? 'N/A',
                'timestamp' => now(),
            ]);

            $monthlyRecord = Log::select(
                DB::raw("DATE_FORMAT(time_in, '%Y %M') as month"),
                DB::raw('COUNT(*) as count')
            )
                ->where('time_in', '!=', null)
                ->groupBy(DB::raw("DATE_FORMAT(time_in, '%Y %M')"))
                ->orderBy(DB::raw("MIN(time_in)"))
                ->limit(12)
                ->get();

            LogFacade::info('Analytics: Monthly users data fetched successfully', [
                'records_count' => $monthlyRecord->count(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            LogFacade::debug('Analytics: Monthly users data details', [
                'data' => $monthlyRecord->toArray(),
                'user_id' => Auth::id(),
            ]);

            return response()->json($monthlyRecord);
        } catch (\Exception $e) {
            LogFacade::error('Analytics: Error fetching monthly users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);
            return response()->json(['error' => 'Unable to fetch monthly users data'], 500);
        }
    }

    /**
     * Fetch the total count of books.
     *
     * This function fetches the total count of books stored in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalBooks()
    {
        try {
            LogFacade::info('Analytics: Fetching total books count', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->full_name ?? 'N/A',
                'timestamp' => now(),
            ]);

            $totalBooks = Book::count();

            LogFacade::info('Analytics: Total books count fetched successfully', [
                'total_books' => $totalBooks,
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json(['total_books' => $totalBooks]);
        } catch (\Exception $e) {
            LogFacade::error('Analytics: Error fetching total books', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);
            return response()->json(['error' => 'Unable to fetch total books count'], 500);
        }
    }

    /**
     * Fetches the transaction history of the library, including the count of borrowed, reserved and returned books per month for the past 12 months.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTransactionHistory(Request $request)
    {
        $now = Carbon::now();
        $startDT = $now->copy()->subMonths(11)->startOfMonth();
        $endDT = $now->copy()->endOfMonth();

        LogFacade::info('Analytics: Fetching transaction history', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'start_date' => $startDT->toDateString(),
            'end_date' => $endDT->toDateString(),
            'timestamp' => now(),
        ]);

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

        LogFacade::debug('Analytics: Generated months list for transaction history', [
            'months_count' => $months->count(),
            'user_id' => Auth::id(),
        ]);

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

        LogFacade::debug('Analytics: Transaction records retrieved', [
            'records_count' => $records->count(),
            'user_id' => Auth::id(),
        ]);

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

            LogFacade::debug('Analytics: Trimmed leading zero months', [
                'first_non_zero_index' => $firstNonZeroIndex,
                'remaining_months' => count($labels),
                'user_id' => Auth::id(),
            ]);
        }

        LogFacade::info('Analytics: Transaction history fetched successfully', [
            'months_count' => count($labels),
            'total_transactions' => array_sum($total),
            'total_borrowed' => array_sum($borrowed),
            'total_returned' => array_sum($returned),
            'total_reserved' => array_sum($reserved),
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'labels'      => $labels,
            'total'       => $total,
            'borrowed'    => $borrowed,
            'returned'    => $returned,
            'reserved'    => $reserved,
        ]);
    }

    /**
     * Fetches the yearly count of acquired books.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchYearlyAquiredBooks()
    {
        LogFacade::info('Analytics: Fetching yearly acquired books', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'timestamp' => now(),
        ]);

        $yearlyRecord = Book::select(
            DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"))
            ->orderBy(DB::raw("MIN(created_at)"))
            ->get();

        LogFacade::info('Analytics: Yearly acquired books fetched successfully', [
            'years_count' => $yearlyRecord->count(),
            'total_books' => $yearlyRecord->sum('count'),
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        LogFacade::debug('Analytics: Yearly acquired books details', [
            'data' => $yearlyRecord->toArray(),
            'user_id' => Auth::id(),
        ]);

        return response()->json($yearlyRecord);
    }

    /**
     * Fetches the growth of registered users (monthly or yearly).
     *
     * This function fetches the count of students, employees, and visitors registered
     * per month (past 12 months) or per year and returns the data in a JSON response for a line graph.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRegisteredUsers(Request $request)
    {
        $period = $request->query('period', 'monthly'); // 'monthly' or 'yearly'

        LogFacade::info('Analytics: Fetching registered users growth', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'period' => $period,
            'timestamp' => now(),
        ]);

        if ($period === 'yearly') {
            return $this->fetchRegisteredUsersYearly();
        }

        return $this->fetchRegisteredUsersMonthly();
    }

    /**
     * Fetches the monthly growth of registered users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function fetchRegisteredUsersMonthly()
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

        // Fetch students monthly count
        $studentsData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'student');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDT, $endDT])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // Fetch employees monthly count
        $employeesData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'employee');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDT, $endDT])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // Fetch visitors monthly count
        $visitorsData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'visitor');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDT, $endDT])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // Get total users registered BEFORE the start date (base counts)
        $baseStudents = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'student');
        })->where('created_at', '<', $startDT)->count();

        $baseEmployees = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'employee');
        })->where('created_at', '<', $startDT)->count();

        $baseVisitors = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'visitor');
        })->where('created_at', '<', $startDT)->count();

        $labels = [];
        $students = [];
        $employees = [];
        $visitors = [];

        // Running totals starting from base counts
        $runningStudents = $baseStudents;
        $runningEmployees = $baseEmployees;
        $runningVisitors = $baseVisitors;

        foreach ($months as $m) {
            $labels[] = $m['label'];

            // Add this month's registrations to the running total
            $runningStudents += isset($studentsData[$m['key']]) ? (int) $studentsData[$m['key']]->count : 0;
            $runningEmployees += isset($employeesData[$m['key']]) ? (int) $employeesData[$m['key']]->count : 0;
            $runningVisitors += isset($visitorsData[$m['key']]) ? (int) $visitorsData[$m['key']]->count : 0;

            $students[] = $runningStudents;
            $employees[] = $runningEmployees;
            $visitors[] = $runningVisitors;
        }

        // Trim leading months where all cumulative values are zero
        $firstNonZeroIndex = null;
        foreach ($labels as $i => $label) {
            if ($students[$i] > 0 || $employees[$i] > 0 || $visitors[$i] > 0) {
                $firstNonZeroIndex = $i;
                break;
            }
        }
        if (!is_null($firstNonZeroIndex)) {
            $labels = array_slice($labels, $firstNonZeroIndex);
            $students = array_slice($students, $firstNonZeroIndex);
            $employees = array_slice($employees, $firstNonZeroIndex);
            $visitors = array_slice($visitors, $firstNonZeroIndex);
        }

        LogFacade::info('Analytics: Registered users monthly growth fetched successfully', [
            'months_count' => count($labels),
            'total_students' => end($students) ?: 0,
            'total_employees' => end($employees) ?: 0,
            'total_visitors' => end($visitors) ?: 0,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'labels' => $labels,
            'students' => $students,
            'employees' => $employees,
            'visitors' => $visitors
        ]);
    }

    /**
     * Fetches the yearly growth of registered users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function fetchRegisteredUsersYearly()
    {
        // Get the year range from the earliest user to current year
        $earliestUser = User::orderBy('created_at', 'asc')->first();
        $startYear = $earliestUser ? Carbon::parse($earliestUser->created_at)->year : Carbon::now()->year;
        $endYear = Carbon::now()->year;

        // Build years list
        $years = collect();
        for ($year = $startYear; $year <= $endYear; $year++) {
            $years->push([
                'key' => (string) $year,
                'label' => (string) $year,
            ]);
        }

        // Fetch students yearly count
        $studentsData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'student');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->keyBy('year');

        // Fetch employees yearly count
        $employeesData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'employee');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->keyBy('year');

        // Fetch visitors yearly count
        $visitorsData = User::whereHas('privileges', function ($query) {
            $query->where('user_type', 'visitor');
        })
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y') as year"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->keyBy('year');

        $labels = [];
        $students = [];
        $employees = [];
        $visitors = [];

        // Running totals for cumulative growth
        $runningStudents = 0;
        $runningEmployees = 0;
        $runningVisitors = 0;

        foreach ($years as $y) {
            $labels[] = $y['label'];

            // Add this year's registrations to the running total
            $runningStudents += isset($studentsData[$y['key']]) ? (int) $studentsData[$y['key']]->count : 0;
            $runningEmployees += isset($employeesData[$y['key']]) ? (int) $employeesData[$y['key']]->count : 0;
            $runningVisitors += isset($visitorsData[$y['key']]) ? (int) $visitorsData[$y['key']]->count : 0;

            $students[] = $runningStudents;
            $employees[] = $runningEmployees;
            $visitors[] = $runningVisitors;
        }

        // Trim leading years where all cumulative values are zero
        $firstNonZeroIndex = null;
        foreach ($labels as $i => $label) {
            if ($students[$i] > 0 || $employees[$i] > 0 || $visitors[$i] > 0) {
                $firstNonZeroIndex = $i;
                break;
            }
        }
        if (!is_null($firstNonZeroIndex)) {
            $labels = array_slice($labels, $firstNonZeroIndex);
            $students = array_slice($students, $firstNonZeroIndex);
            $employees = array_slice($employees, $firstNonZeroIndex);
            $visitors = array_slice($visitors, $firstNonZeroIndex);
        }

        LogFacade::info('Analytics: Registered users yearly growth fetched successfully', [
            'years_count' => count($labels),
            'total_students' => end($students) ?: 0,
            'total_employees' => end($employees) ?: 0,
            'total_visitors' => end($visitors) ?: 0,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'labels' => $labels,
            'students' => $students,
            'employees' => $employees,
            'visitors' => $visitors
        ]);
    }

    /**
     * Fetches the top 6 most visited students for each level.
     *
     * This function fetches the top 6 most visited students for each level and returns the result in a JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mostVisitedStudents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        LogFacade::info('Analytics: Fetching most visited students', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'start_date' => $start,
            'end_date' => $end,
            'timestamp' => now(),
        ]);

        try {
            $levels = range(7, 12);
            $results = collect();

            $hasRange = false;
            $startDT = null;
            $endDT = null;

            if ($start && $end) {
                try {
                    $startDT = Carbon::parse($start)->startOfDay();
                    $endDT = Carbon::parse($end)->endOfDay();
                    $hasRange = true;

                    LogFacade::debug('Analytics: Date range parsed for most visited students', [
                        'start_datetime' => $startDT->toDateTimeString(),
                        'end_datetime' => $endDT->toDateTimeString(),
                        'user_id' => Auth::id(),
                    ]);
                } catch (\Throwable $e) {
                    LogFacade::warning('Analytics: Failed to parse date range, using current year', [
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
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

                LogFacade::debug('Analytics: Fetched top students for level', [
                    'level' => $level,
                    'students_count' => $topStudents->count(),
                    'user_id' => Auth::id(),
                ]);

                $results->push([
                    'level' => $level,
                    'students' => $topStudents,
                ]);
            }

            LogFacade::info('Analytics: Most visited students fetched successfully', [
                'levels_processed' => count($levels),
                'total_students' => $results->sum(fn($r) => count($r['students'])),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json($results->values(), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Throwable $e) {
            LogFacade::error('Analytics: Error fetching most visited students', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Fetch the top 3 students with the most borrowed books per grade level within a given date range.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function mostBorrowedStudents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        LogFacade::info('Analytics: Fetching most borrowed students', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'start_date' => $start,
            'end_date' => $end,
            'timestamp' => now(),
        ]);

        try {
            $levels = range(7, 12);
            $results = collect();

            $hasRange = false;
            $startDT = null;
            $endDT = null;

            if ($start && $end) {
                try {
                    $startDT = Carbon::parse($start)->startOfDay();
                    $endDT = Carbon::parse($end)->endOfDay();
                    $hasRange = true;

                    LogFacade::debug('Analytics: Date range parsed for most borrowed students', [
                        'start_datetime' => $startDT->toDateTimeString(),
                        'end_datetime' => $endDT->toDateTimeString(),
                        'user_id' => Auth::id(),
                    ]);
                } catch (\Throwable $e) {
                    LogFacade::warning('Analytics: Failed to parse date range, using current year', [
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
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

                LogFacade::debug('Analytics: Fetched top borrowers for level', [
                    'level' => $level,
                    'students_count' => $topStudents->count(),
                    'user_id' => Auth::id(),
                ]);

                $results->push([
                    'level' => $level,
                    'students' => $topStudents,
                ]);
            }

            LogFacade::info('Analytics: Most borrowed students fetched successfully', [
                'levels_processed' => count($levels),
                'total_students' => $results->sum(fn($r) => count($r['students'])),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json($results->values(), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Throwable $e) {
            LogFacade::error('Analytics: Error fetching most borrowed students', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Fetch the top 5 books with the most borrowed transactions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function topBooksBorrowed()
    {
        LogFacade::info('Analytics: Fetching top books borrowed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'timestamp' => now(),
        ]);

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

            LogFacade::info('Analytics: Top books borrowed fetched successfully', [
                'books_count' => $topBooks->count(),
                'total_borrows' => $topBooks->sum('total_borrows'),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            LogFacade::debug('Analytics: Top books borrowed details', [
                'data' => $topBooks->toArray(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'labels' => $topBooks->pluck('title'),
                'counts' => $topBooks->pluck('total_borrows'),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Throwable $e) {
            LogFacade::error('Analytics: Error fetching top books borrowed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Fetch the top 5 categories with the most borrowed transactions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function topCategoriesBorrowed()
    {
        LogFacade::info('Analytics: Fetching top categories borrowed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name ?? 'N/A',
            'timestamp' => now(),
        ]);

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

            LogFacade::info('Analytics: Top categories borrowed fetched successfully', [
                'categories_count' => $topCategories->count(),
                'total_borrows' => $topCategories->sum('total_borrows'),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            LogFacade::debug('Analytics: Top categories borrowed details', [
                'data' => $topCategories->toArray(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'labels' => $topCategories->pluck('category'),
                'counts' => $topCategories->pluck('total_borrows'),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Throwable $e) {
            LogFacade::error('Analytics: Error fetching top categories borrowed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
