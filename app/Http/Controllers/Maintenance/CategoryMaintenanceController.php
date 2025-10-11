<?php

namespace App\Http\Controllers\Maintenance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryMaintenanceController extends Controller
{
    /**
     * Index of categories
     * 
     * This function is used to fetch all categories from the database and
     * pass it to the view.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = Category::all();
        return view('maintenance.categories.index', compact('categories'));
    }
    /**
     * Store a new category
     *
     * This function is used to store a new category in the database.
     * It first validates the request data, then checks if a category with the same name already exists.
     * If it does, it redirects back with a warning that the category already exists.
     * If it doesn't, it attempts to create the new category in the database.
     * If the creation fails, it rolls back the database transaction and redirects back with an error message.
     * If the creation succeeds, it commits the database transaction and redirects back with a success message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:50',
            'legend'                    => 'required|string|max:255',
            'borrow_duration_days_add'  => 'required|integer|min:0|max:999',
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
                'name'                  => $request->input('name'),
                'legend'                => $request->input('legend'),
                'borrow_duration_days'  => $request->input('borrow_duration_days_add'),
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Error occurred while creating category.');
        }
        DB::commit();
        return redirect()->route('maintenance.categories')->with('toast-success', 'Category updated successfully.');
    }
    /**
     * This function is used to update a category in the database.
     * It first validates the request data, then checks if a category with the same name or legend already exists.
     * If it does, it redirects back with a warning that the category already exists.
     * If it doesn't, it attempts to update the category in the database.
     * If the update fails, it rolls back the database transaction and redirects back with an error message.
     * If the update succeeds, it commits the database transaction and redirects back with a success message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:50',
            'legend'                    => 'required|string|max:255',
            'borrow_duration_days_edit' => 'required|integer|min:0|max:999',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $category = Category::findOrFail($request->input('edit_category_id'));
            $category->name                 = $request->input('name');
            $category->legend               = $request->input('legend');
            $category->borrow_duration_days = $request->input('borrow_duration_days_edit');
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
    /**
     * Deletes a category from the database.
     *
     * This function first starts a database transaction, then attempts to find and delete the category with the given id.
     * If the deletion fails, it rolls back the database transaction and redirects back with an error message.
     * If the deletion succeeds, it commits the database transaction and redirects back with a success message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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
