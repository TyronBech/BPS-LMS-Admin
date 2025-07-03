<?php

namespace App\Http\Controllers\Maintenance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PenaltyRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenaltyRuleController extends Controller
{
    public function index()
    {
        $rules = PenaltyRule::all();
        return view('maintenance.penalties.index', compact('rules'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'          => 'required|string',
            'description'   => 'required|string',
            'rate'          => 'required|numeric|min:0',
            'per_day'       => 'required|numeric|min:0|max:99',
        ]);
        if($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            PenaltyRule::create([
                'type'          => $request->input('type'),
                'description'   => $request->input('description') ?? null,
                'rate'          => $request->input('rate'),
                'per_day'       => $request->input('per_day'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Failed to create penalty rule. Please try again.');
        }
        DB::commit();
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule created successfully.');
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'          => 'required|string|max:50',
            'description'   => 'required|string|max:255',
            'rate'          => 'required|numeric|min:0',
            'per_day'       => 'required|boolean',
        ]);
        if($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $rule = PenaltyRule::findOrFail($request->input('edit_rule_id'));
            $rule->update([
                'type'          => $request->input('type'),
                'description'   => $request->input('description') ?? null,
                'rate'          => $request->input('rate'),
                'per_day'       => $request->input('per_day'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Failed to update penalty rule. Please try again.');
        }
        DB::commit();
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule updated successfully.');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $rule = PenaltyRule::findOrFail($request->input('delete_rule_id'));
            $rule->delete();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Failed to delete penalty rule. Please try again.');
        }
        DB::commit();
        return redirect()->route('maintenance.penalty-rules')->with('toast-success', 'Penalty rule deleted successfully.');
    }
}
