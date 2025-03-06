<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookMaintenanceController extends Controller
{
    public function index()
    {
        $books = Book::with('category')
                    ->orderBy(DB::raw('DATE(updated_at)'), 'desc')
                    ->orderBy(DB::raw('TIME(updated_at)'), 'desc')
                    ->get();
        return view('maintenance.books.books', compact('books'));
    }
    public function create()
    {
        $categories     = Category::all()->pluck('name', 'id');
        $condition      = $this->extract_enums('books', 'condition_status');
        $availability   = $this->extract_enums('books', 'availability_status');     
        $remarks        = $this->extract_enums('books', 'remarks'); 
        return view('maintenance.books.create', compact('categories', 'condition', 'availability', 'remarks'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'required|string|max:50',
            'barcode'           => 'sometimes',
            'title'             => 'required|string|max:255',
            'authors'           => 'sometimes',
            'edition'           => 'sometimes',
            'publication'       => 'required|string|max:255',
            'publisher'         => 'required|string|max:255',
            'copyright'         => 'required|string|max:50',
            'cover_image'       => 'sometimes',
            'digital_copy_url'  => 'sometimes',
            'remarks'           => 'required',
            'category'          => 'required|in:'.implode(',', Category::all()->pluck('id')->toArray()),
            'condition'         => 'required|in:'.implode(',', $this->extract_enums('books', 'condition_status')),
            'availability'      => 'required|in:'.implode(',', $this->extract_enums('books', 'availability_status')),
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            Book::create([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number'),
                'barcode'               => $request->input('barcode'),
                'title'                 => $request->input('title'),
                'author'                => $request->input('authors'),
                'edition'               => $request->input('edition'),
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'remarks'               => $request->input('remarks'),
                'category_id'           => $request->input('category'),
                'condition_status'      => $request->input('condition'),
                'availability_status'   => $request->input('availability'),
                'created_at'            => now(),
                'updated_at'            => now()
            ]);
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Book added successfully');
    }
    public function edit(Request $request)
    {
        $book = null;
        try{
            $accession = array_keys($request->all())[0]; 
            $book = Book::where('accession', $accession)->first();
            $categories     = Category::all()->pluck('name', 'id');
            $condition      = $this->extract_enums('books', 'condition_status');
            $availability   = $this->extract_enums('books', 'availability_status');     
            $remarks        = $this->extract_enums('books', 'remarks');
        } catch(\Exception $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.books.edit', compact('book', 'categories', 'condition', 'availability', 'remarks'));
    }
    public function show(Request $request)
    {
        $books = Book::where('accession', $request->input('search-accession'))->get();
        return view('maintenance.books.books', compact('books'));
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'required|string|max:50',
            'barcode'           => 'sometimes',
            'title'             => 'required|string|max:255',
            'authors'           => 'sometimes',
            'edition'           => 'sometimes',
            'publication'       => 'required|string|max:255',
            'publisher'         => 'required|string|max:255',
            'copyright'         => 'required|string|max:50',
            'cover_image'       => 'sometimes',
            'digital_copy_url'  => 'sometimes',
            'remarks'           => 'required',
            'category'          => 'required|in:'.implode(',', Category::all()->pluck('id')->toArray()),
            'condition'         => 'required|in:'.implode(',', $this->extract_enums('books', 'condition_status')),
            'availability'      => 'required|in:'.implode(',', $this->extract_enums('books', 'availability_status')),
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $book = Book::findOrFail($request->input('id'));
            $book->update([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number'),
                'title'                 => $request->input('title'),
                'author'                => $request->input('authors'),
                'edition'               => $request->input('edition'),
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'remarks'               => $request->input('remarks'),
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'updated_at'            => now()
            ]);
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Book updated successfully');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $id = array_keys($request->all())[0];
            Book::find($id)->delete();
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('books')->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('books')->with('toast-success', 'Book deleted successfully');
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
