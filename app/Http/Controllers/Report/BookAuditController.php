<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\BookAudit;
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
        $data = $this->generateData($request, new BookAudit(), false);
        return view('report.audits.books.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'perPage'));
    }
    public function search(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $tableName      = new BookAudit();
        $validator = Validator::make($request->all(), [
            'start'         => 'nullable',
            'end'           => 'nullable',
            'last-name'     => 'nullable',
            'types'         => 'in:ALL,INSERT,UPDATE,DELETE',
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
    private function generateData(Request $request, BookAudit $tableName, $isExport = false)
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
        ])->orderBy('created_at', 'desc');
        if (strlen($fromInputDate) > 0) {
            $fromInputDate = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $toInputDate = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $data->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), [$fromInputDate, $toInputDate]);
        }
        if ($types !== 'ALL') {
            $data->where(DB::raw('upper(' . $tableName->getTable() . '.change_type)'), $types);
        }
        if ($isExport) {
            $data = $data->orderBy(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), 'asc')
                ->orderBy(DB::raw('TIME(' . $tableName->getTable() . '.created_at)'), 'asc')
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
