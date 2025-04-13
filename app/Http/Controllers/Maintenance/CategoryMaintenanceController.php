<?php

namespace App\Http\Controllers\Maintenance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryMaintenanceController extends Controller
{
    public function index()
    {
        $categories = Category::pluck('name', 'legend');
        return view('maintenance.categories.index', compact('categories'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:50',
            'legend'    => 'required|string|max:255',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(Category::where('name', $request->name)->exists()) {
            return redirect()->route('maintenance.categories')->with('toast-warning', 'Category already exists.');
        }
        DB::beginTransaction();
        try{
            Category::create([
                'name'  => $request->input('name'),
                'legend'=> $request->input('legend'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while creating category.');
        }
        DB::commit();
        return redirect()->route('maintenance.categories')->with('success', 'Category updated successfully.');
    }
}
