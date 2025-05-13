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
        $categories = Category::all();
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
                'name'      => $request->input('name'),
                'legend'    => $request->input('legend'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while creating category.');
        }
        DB::commit();
        return redirect()->route('maintenance.categories')->with('toast-success', 'Category updated successfully.');
    }
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:50',
            'legend'    => 'required|string|max:255',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $category = Category::findOrFail($request->input('edit_category_id'));
            $category->name     = $request->input('name');
            $category->legend   = $request->input('legend');
            $category->save();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            if($e->getCode() == 23000){
                return redirect()->back()->with('toast-warning', 'Category legend or name already exists.');
            }
            return redirect()->back()->with('toast-error', 'Error occurred while updating category.');
        }
        DB::commit();
        return redirect()->route('maintenance.categories')->with('toast-success', 'Category updated successfully.');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $category = Category::findOrFail($request->input('delete_category_id'));
            $category->delete();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while deleting category.');
        }
        DB::commit();
        return redirect()->route('maintenance.categories')->with('toast-success', 'Category deleted successfully.');
    }
}
