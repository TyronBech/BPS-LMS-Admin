<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\BookAudit;
use App\Models\AuditTrail;
use App\Models\Book;
use App\Models\Category;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookAuditController extends Controller
{
    public function index(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $data = $this->generateData($request, new AuditTrail(), false);
        return view('report.audits.books.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'perPage'));
    }
    public function search(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $tableName      = new AuditTrail();
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable|date',
            'end'           => 'nullable|date',
            'types'         => 'in:ALL,INSERT,UPDATE,DELETE',
            'perPage'       => 'nullable|numeric|in:10,25,50'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, $tableName, true);
            //$this->generatePDF($data);
            return redirect()->route('report.audit-trail.books')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, $tableName, true);
            //$this->exportExcel($data);
            return redirect()->route('report.audit-trail.books')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, $tableName, false);
        return view('report.audits.books.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'perPage'));
    }
    private function generateData(Request $request, AuditTrail $tableName, $isExport = false)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $types          = strtoupper($request->input('types', 'ALL'));
        $perPage        = $request->input('perPage', 10);
        $data           = $tableName::with([
            'book' => function ($query) {
                $query->withTrashed();
            },
            'changedBy' => function ($query) {
                $query->withTrashed();
            },
            'oldCategory' => function ($query) {
                $query->withTrashed();
            },
            'newCategory' => function ($query) {
                $query->withTrashed();
            }
        ])->where(function ($query) {
            $query->where('source_table', (new Book())->getTable())
                ->orWhere('source_table', (new Category())->getTable());
        });
        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $from = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $to = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $data->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), [$from, $to]);
        }
        if ($types != 'ALL') {
            $data->where($tableName->getTable() . '.action_type', $types);
        }
        if ($isExport) {
            $data = $data->orderBy(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), 'desc')
                ->orderBy(DB::raw('TIME(' . $tableName->getTable() . '.created_at)'), 'desc')
                ->get();
        } else {
            $data = $data->paginate($perPage)
                ->appends([
                    'start' => $fromInputDate,
                    'end' => $toInputDate,
                    'types' => $types,
                    'perPage' => $perPage,
                ]);
        }
        return $data;
    }
}
