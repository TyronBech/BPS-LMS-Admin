<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $types          = $request->input('types', 'ALL');
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $perPage        = $request->input('perPage', 10);
        $data           = $this->generateData($request, new AuditTrail(), false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'perPage'));
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
            'types'         => 'in:ALL,INSERT,UPDATE,DELETE,LOGIN,LOGOUT',
            'perPage'       => 'nullable|numeric|in:10,25,50,100,250,500,1000',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if ($request->input('submit') == 'pdf') {
            $data = $this->generateData($request, $tableName, true);
            //$this->generatePDF($data);
            return redirect()->route('report.audit-trail.users')->with('toast-success', 'Successfully exported to PDF');
        } else if ($request->input('submit') == 'excel') {
            $data = $this->generateData($request, $tableName, true);
            //$this->exportExcel($data);
            return redirect()->route('report.audit-trail.users')->with('toast-success', 'Successfully exported to Excel');
        }
        $data = $this->generateData($request, $tableName, false);
        return view('report.audits.index', compact('data', 'types', 'fromInputDate', 'toInputDate', 'perPage'));
    }
    private function generateData(Request $request, AuditTrail $tableName, $isExport = false)
    {
        $fromInputDate  = $request->input('start', '');
        $toInputDate    = $request->input('end', '');
        $types          = strtoupper($request->input('types', 'ALL'));
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
        $data->orderBy($tableName->getTable() . '.created_at', 'desc');
        if (!empty($fromInputDate) && !empty($toInputDate)) {
            $from = DateTime::createFromFormat('m/d/Y', $fromInputDate)->format('Y-m-d');
            $to = DateTime::createFromFormat('m/d/Y', $toInputDate)->format('Y-m-d');
            $data->whereBetween(DB::raw('DATE(' . $tableName->getTable() . '.created_at)'), [$from, $to]);
        }
        if ($types != 'ALL') {
            $data->where($tableName->getTable() . '.action_type', $types);
        }
        if ($isExport) {
            $data = $data->get();
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
