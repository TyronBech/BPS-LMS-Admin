<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PrivilegeMaintenanceController extends Controller
{
    public function index()
    {
        $privileges = UserGroup::all();
        $durations = $this->extract_enums((new UserGroup)->getTable(), 'duration_type');
        return view('maintenance.privileges.index', compact('privileges', 'durations'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type'                 => 'required|string|max:50',
            'category'                  => 'required|string|max:50',
            'max_book_allowed_add'      => 'required|integer|min:0|max:999',
            'renewal_limit_add'         => 'required|integer|min:0|max:999',
            'duration_type'             => 'required|string|max:50|in:'.implode(',', $this->extract_enums((new UserGroup)->getTable(), 'duration_type')),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            UserGroup::create([
                'user_type'             => $request->input('user_type'),
                'category'              => $request->input('category'),
                'max_book_allowed'      => $request->input('max_book_allowed_add'),
                'renewal_limit'         => $request->input('renewal_limit_add'),
                'duration_type'         => $request->input('duration_type'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while creating privilege.');
        }
        DB::commit();
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege created successfully.');
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type'                     => 'required|string|max:50',
            'category'                      => 'required|string|max:50',
            'max_book_allowed_update'       => 'required|integer|min:0|max:999',
            'renewal_limit_update'          => 'required|integer|min:0|max:999',
            'duration_type'                 => 'required|string|max:50|in:'.implode(',', $this->extract_enums((new UserGroup)->getTable(), 'duration_type')),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $privilege = UserGroup::findOrFail($request->input('edit_privilege_id'));
            $privilege->update([
                'user_type'             => $request->input('user_type'),
                'category'              => $request->input('category'),
                'max_book_allowed'      => $request->input('max_book_allowed_update'),
                'renewal_limit'         => $request->input('renewal_limit_update'),
                'duration_type'         => $request->input('duration_type'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while updating privilege.');
        }
        DB::commit();
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege updated successfully.');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $privilege = UserGroup::findOrFail($request->input('delete_privilege_id'));
            $privilege->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while deleting privilege.');
        }
        DB::commit();
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege deleted successfully.');
    }
    private function extract_enums($table, $columnName){
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];

        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        return $enumValues;
    }
}
