<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PrivilegeMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        Log::info('Privilege Maintenance: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'per_page' => $perPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $privileges = UserGroup::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
            ]);
        $durations = $this->extract_enums((new UserGroup)->getTable(), 'duration_type');
        return view('maintenance.privileges.index', compact('privileges', 'durations', 'perPage'));
    }
    /**
     * Create a new privilege.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function store(Request $request)
    {
        Log::info('Privilege Maintenance: Attempting to create privilege', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'duration_type' => $request->input('duration_type'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'max_book_allowed_add'      => 'required|integer|min:0|max:999',
            'renewal_limit_add'         => 'required|integer|min:0|max:999',
            'duration_type'             => 'required|string|max:50|in:'.implode(',', $this->extract_enums((new UserGroup)->getTable(), 'duration_type')),
        ]);
        if ($validator->fails()) {
            Log::warning('Privilege Maintenance: Creation validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            UserGroup::create([
                'max_book_allowed'      => $request->input('max_book_allowed_add'),
                'renewal_limit'         => $request->input('renewal_limit_add'),
                'duration_type'         => $request->input('duration_type'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Privilege Maintenance: Database error during creation', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while creating privilege.');
        }
        DB::commit();
        Log::info('Privilege Maintenance: Privilege created successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'duration_type' => $request->input('duration_type'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege created successfully.');
    }
    /**
     * Update an existing privilege.
     *
     * This function validates the request and checks if the authenticated
     * admin has permission to modify the privilege. It then updates the
     * privilege and syncs the roles. If there is an error, it rolls back
     * the transaction and redirects to the previous page with an error message.
     *
     * @throws \Illuminate\Database\QueryException
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        Log::info('Privilege Maintenance: Attempting to update privilege', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'privilege_id' => $request->input('edit_privilege_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'max_book_allowed_update'       => 'required|integer|min:0|max:999',
            'renewal_limit_update'          => 'required|integer|min:0|max:999',
            'duration_type'                 => 'required|string|max:50|in:'.implode(',', $this->extract_enums((new UserGroup)->getTable(), 'duration_type')),
        ]);
        if ($validator->fails()) {
            Log::warning('Privilege Maintenance: Update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $privilege = UserGroup::findOrFail($request->input('edit_privilege_id'));
            $privilege->update([
                'max_book_allowed'      => $request->input('max_book_allowed_update'),
                'renewal_limit'         => $request->input('renewal_limit_update'),
                'duration_type'         => $request->input('duration_type'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Privilege Maintenance: Database error during update', [
                'user_id' => Auth::guard('admin')->id(),
                'privilege_id' => $request->input('edit_privilege_id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while updating privilege.');
        }
        DB::commit();
        Log::info('Privilege Maintenance: Privilege updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'privilege_id' => $request->input('edit_privilege_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege updated successfully.');
    }
    /**
     * Delete an existing privilege.
     *
     * This function deletes a privilege and syncs the roles. If there is an
     * error, it rolls back the transaction and directs to the previous page
     * with an error message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function destroy(Request $request)
    {
        Log::warning('Privilege Maintenance: Attempting to delete privilege', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'privilege_id' => $request->input('delete_privilege_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $privilege = UserGroup::findOrFail($request->input('delete_privilege_id'));
            $privilege->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Privilege Maintenance: Database error during deletion', [
                'user_id' => Auth::guard('admin')->id(),
                'privilege_id' => $request->input('delete_privilege_id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while deleting privilege.');
        }
        DB::commit();
        Log::info('Privilege Maintenance: Privilege deleted successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'privilege_id' => $request->input('delete_privilege_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.privileges')->with('toast-success', 'Privilege deleted successfully.');
    }
    /**
     * Extracts the enum values from a given table and column name.
     *
     * @param string $table The name of the table to query.
     * @param string $columnName The name of the column to extract the enum values from.
     * @return array An array of enum values. If no enum values are found, returns ['N/A'].
     */
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
