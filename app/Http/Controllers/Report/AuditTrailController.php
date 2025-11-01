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

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $tableType      = $request->input('tableType', 'All');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $data           = $this->generateData($request, new AuditTrail(), false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'tableType', 'perPage'));
    }
    public function search(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $tableType      = $request->input('tableType', 'All');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $tableName      = new AuditTrail();
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date',
            'types'         => 'in:ALL,INSERT,UPDATE,DELETE,LOGIN,LOGOUT',
            'tableType'     => 'in:All,Users,Books,Transactions,Sessions',
            'perPage'       => 'nullable|numeric|in:10,25,50,100,250,500,1000',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        $data = $this->generateData($request, $tableName, false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'tableType', 'perPage'));
    }
    private function generateData(Request $request, AuditTrail $tableName, $isExport = false)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $types          = strtoupper($request->input('types', 'ALL'));
        $tableType      = $request->input('tableType', 'All');
        $perPage        = $request->input('perPage', 10);
        $data           = $tableName::with([
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
            $data->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), [$start, $end]);
        }
        if ($types != 'ALL') {
            $data->where($tableName->getTable() . '.action_type', $types);
        }
        if ($tableType != 'All') {
            if ($tableType == 'Users') {
                $data->whereIn($tableName->getTable() . '.source_table', [
                    (new User())->getTable(),
                    (new EmployeeDetail())->getTable(),
                    (new StudentDetail())->getTable(),
                    (new VisitorDetail())->getTable()
                ]);
            } elseif ($tableType == 'Books') {
                $data->whereIn($tableName->getTable() . '.source_table', [
                    (new Book())->getTable(),
                    (new Category())->getTable()
                ]);
            } elseif ($tableType == 'Transactions') {
                $data->whereIn($tableName->getTable() . '.source_table', [
                    (new Transaction())->getTable(),
                ]);
            } elseif ($tableType == 'Sessions') {
                $data->whereIn($tableName->getTable() . '.source_table', [
                    'sessions',
                ]);
            }
        }
        $data->orderBy($tableName->getTable() . '.created_at', 'desc')
            ->orderBy($tableName->getTable() . '.id', 'desc');
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
