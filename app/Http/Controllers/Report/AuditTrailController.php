<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Book;
use App\Models\Category;
use App\Models\EmployeeDetail;
use App\Models\StudentDetail;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VisitorDetail;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuditTrailController extends Controller
{
    /**
     * This function is used to handle the page request for the audit trail report.
     * It takes in the request object and extracts the types, table type, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, types, start date, end date, table type and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $tableType      = $request->input('tableType', 'All');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);

        Log::info('Audit Trail: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => [
                'types' => $types,
                'table_type' => $tableType,
                'start_date' => $fromInputDate,
                'end_date' => $toInputDate,
            ],
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'types'     => 'nullable|string|in:ALL,INSERT,UPDATE,DELETE,LOGIN,LOGOUT',
            'tableType' => 'nullable|string|in:All,Users,Books,Transactions,Sessions',
            'start'     => 'nullable|date',
            'end'       => 'nullable|date|after_or_equal:start',
            'perPage'   => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $data = $this->generateData($request, new AuditTrail(), false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'tableType', 'perPage'));
    }
    /**
     * This function is used to handle the search request for the audit trail report.
     * It takes in the request object and extracts the types, table type, start date, end date and page size from the request.
     * It then logs an info message with the user id, user name, filters, ip address and timestamp.
     * If the validation fails, it logs a warning message with the user id, errors, ip address and timestamp.
     * Finally, it generates the data for the report and returns the view with the data, types, start date, end date, table type and page size.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $tableType      = $request->input('tableType', 'All');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);

        Log::info('Audit Trail: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'filters' => $request->only(['start', 'end', 'types', 'tableType', 'perPage']),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $tableName      = new AuditTrail();
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date|after_or_equal:start',
            'types'         => 'in:ALL,INSERT,UPDATE,DELETE,LOGIN,LOGOUT',
            'tableType'     => 'in:All,Users,Books,Transactions,Sessions',
            'perPage'       => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Audit Trail: Search validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        $data = $this->generateData($request, $tableName, false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'tableType', 'perPage'));
    }
    /**
     * Generates data for the audit trail report based on the given request inputs.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AuditTrail $model
     * @param bool $isExport
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    private function generateData(Request $request, AuditTrail $model, $isExport = false)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $types          = strtoupper($request->input('types', 'ALL'));
        $tableType      = $request->input('tableType', 'All');
        $perPage        = $request->input('perPage', 10);
        $data           = $model::with([
            'user' => function ($query) {
                $query->withTrashed();
            },
            'visitor' => function ($query) {
                $query->withTrashed();
            },
            'changedBy' => function ($query) {
                $query->withTrashed();
            },
            'oldPrivilege' => function ($query) {
                $query->withTrashed();
            },
            'newPrivilege' => function ($query) {
                $query->withTrashed();
            },
            'book' => function ($query) {
                $query->withTrashed();
            },
            'oldCategory' => function ($query) {
                $query->withTrashed();
            },
            'newCategory' => function ($query) {
                $query->withTrashed();
            },
            'transaction' => function ($query) {
                $query->withTrashed();
            },
            'oldBook' => function ($query) {
                $query->withTrashed();
            },
            'newBook' => function ($query) {
                $query->withTrashed();
            },
            'oldUser' => function ($query) {
                $query->withTrashed();
            },
            'newUser' => function ($query) {
                $query->withTrashed();
            }
        ]);
        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $start = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $end = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $data->whereBetween(DB::raw('DATE(' . $model->getTable() . '.created_at)'), [$start, $end]);
        }
        if ($types != 'ALL') {
            $data->where($model->getTable() . '.action_type', $types);
        }
        if ($tableType != 'All') {
            if ($tableType == 'Users') {
                $data->whereIn($model->getTable() . '.source_table', [
                    (new User())->getTable(),
                    (new EmployeeDetail())->getTable(),
                    (new StudentDetail())->getTable(),
                    (new VisitorDetail())->getTable()
                ]);
            } elseif ($tableType == 'Books') {
                $data->whereIn($model->getTable() . '.source_table', [
                    (new Book())->getTable(),
                    (new Category())->getTable()
                ]);
            } elseif ($tableType == 'Transactions') {
                $data->whereIn($model->getTable() . '.source_table', [
                    (new Transaction())->getTable(),
                ]);
            } elseif ($tableType == 'Sessions') {
                $data->whereIn($model->getTable() . '.source_table', [
                    'sessions',
                ]);
            }
        }
        $data->orderBy($model->getTable() . '.created_at', 'desc')
            ->orderBy($model->getTable() . '.id', 'desc');
        if ($isExport) {
            $data = $data->get();
        } else {
            $data = $data->paginate($perPage)
                ->appends([
                    'start' => $fromInputDate,
                    'end' => $toInputDate,
                    'types' => $types,
                    'perPage' => $perPage,
                    'tableType' => $tableType
                ]);
        }
        return $data;
    }
}
