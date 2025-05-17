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
        return view('maintenance.privileges.index', compact('privileges'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type'                 => 'required|string|max:50',
            'category'                  => 'required|string|max:50',
            'max_book_allowed_add'      => 'required|integer|min:0|max:999',
            'borrow_duration_days_add'  => 'required|integer|min:0|max:999',
            'renewal_limit_add'         => 'required|integer|min:0|max:999',
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
                'borrow_duration_days'  => $request->input('borrow_duration_days'),
                'renewal_limit'         => $request->input('renewal_limit'),
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
            'borrow_duration_days_update'   => 'required|integer|min:0|max:999',
            'renewal_limit_update'          => 'required|integer|min:0|max:999',
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
                'borrow_duration_days'  => $request->input('borrow_duration_days_updare'),
                'renewal_limit'         => $request->input('renewal_limit_update'),
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
}
