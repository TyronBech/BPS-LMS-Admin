<?php

namespace App\Http\Controllers\Maintenance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PenaltyRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PenaltyRuleController extends Controller
{
    /**
     * Displays a list of all penalty rules in the system.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        Log::info('Penalty Rules: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'per_page' => $perPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $rules = PenaltyRule::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
            ]);
        return view('maintenance.penalties.index', compact('rules', 'perPage'));
    }
    /**
     * Creates a new penalty rule in the system.
     * 
     * Validates the request data using the rules specified in the validator.
     * If the validation fails, it redirects back with a warning toast.
     * If the validation succeeds, it creates a new PenaltyRule instance with the provided data.
     * If the creation of the instance fails due to a database query exception,
     * it rolls back the transaction and redirects back with an error toast.
     * If the creation of the instance succeeds, it commits the transaction and
     * redirects back with a success toast.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::info('Penalty Rules: Attempting to create rule', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'type' => $request->input('type'),
            'rate' => $request->input('rate'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'type'          => 'required|string',
            'description'   => 'required|string',
            'rate'          => 'required|numeric|min:0',
            'per_day'       => 'required|integer|in:0,1',
        ]);
        if($validator->fails()) {
            Log::warning('Penalty Rules: Creation validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            PenaltyRule::create([
                'type'          => $request->input('type'),
                'description'   => $request->input('description') ?? null,
                'rate'          => $request->input('rate'),
                'per_day'       => $request->input('per_day'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            Log::error('Penalty Rules: Database error during creation', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to create penalty rule. Please try again.');
        }
        DB::commit();
        Log::info('Penalty Rules: Rule created successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'type' => $request->input('type'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule created successfully.');
    }
    /**
     * Updates an existing penalty rule in the system.
     *
     * Validates the request data using the rules specified in the validator.
     * If the validation fails, it redirects back with a warning toast.
     * If the validation succeeds, it updates the existing PenaltyRule instance with the provided data.
     * If the update of the instance fails due to a database query exception,
     * it rolls back the transaction and directs back with an error toast.
     * If the update of the instance succeeds, it commits the transaction and
     * directs back with a success toast.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        Log::info('Penalty Rules: Attempting to update rule', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'rule_id' => $request->input('edit_rule_id'),
            'type' => $request->input('type'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'type'          => 'required|string|max:50',
            'description'   => 'required|string|max:255',
            'rate'          => 'required|numeric|min:0',
            'per_day'       => 'required|integer|in:0,1 ',
        ]);
        if($validator->fails()) {
            Log::warning('Penalty Rules: Update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $rule = PenaltyRule::findOrFail($request->input('edit_rule_id'));
            $rule->update([
                'type'          => $request->input('type'),
                'description'   => $request->input('description') ?? null,
                'rate'          => $request->input('rate'),
                'per_day'       => $request->input('per_day'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            Log::error('Penalty Rules: Database error during update', [
                'user_id' => Auth::guard('admin')->id(),
                'rule_id' => $request->input('edit_rule_id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to update penalty rule. Please try again.');
        }
        DB::commit();
        Log::info('Penalty Rules: Rule updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'rule_id' => $request->input('edit_rule_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule updated successfully.');
    }
    /**
     * Deletes an existing penalty rule from the system.
     *
     * Validates the request data using the rules specified in the validator.
     * If the validation fails, it redirects back with a warning toast.
     * If the validation succeeds, it deletes the existing PenaltyRule instance with the provided data.
     * If the deletion of the instance fails due to a database query exception,
     * it rolls back the transaction and directs back with an error toast.
     * If the deletion of the instance succeeds, it commits the transaction and
     * directs back with a success toast.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Log::warning('Penalty Rules: Attempting to delete rule', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'rule_id' => $request->input('delete_rule_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $rule = PenaltyRule::findOrFail($request->input('delete_rule_id'));
            $rule->delete();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            Log::error('Penalty Rules: Database error during deletion', [
                'user_id' => Auth::guard('admin')->id(),
                'rule_id' => $request->input('delete_rule_id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to delete penalty rule. Please try again.');
        }
        DB::commit();
        Log::info('Penalty Rules: Rule deleted successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'rule_id' => $request->input('delete_rule_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule deleted successfully.');
    }
}
