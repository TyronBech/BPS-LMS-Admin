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
                    ->orderBy('id', 'asc')
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
        $search = strtolower($request->input('search'));

        $books = Book::where('accession', 'like', '%' . $search . '%')
            ->orWhere('title', 'like', '%' . $search . '%')
            ->orWhere('author', 'like', '%' . $search . '%')
            ->orWhere('publisher', 'like', '%' . $search . '%')
            ->orWhere('place_of_publication', 'like', '%' . $search . '%')
            ->orWhere('edition', 'like', '%' . $search . '%')
            ->orWhere('call_number', 'like', '%' . $search . '%')
            ->orWhere('copyrights', 'like', '%' . $search . '%')
            ->orWhere('digital_copy_url', 'like', '%' . $search . '%')
            ->orWhere('remarks', 'like', '%' . $search . '%')
            ->orWhereHas('category', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->get();
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
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'remarks'               => $request->input('remarks'),
                'category'              => $request->input('category'),
                'condition_status'      => $request->input('condition'),
                'availability_status'   => $request->input('availability'),
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
            $id = $request->input('id');
            Book::find($id)->delete();
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('maintenance.books')->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.books')->with('toast-success', 'Book deleted successfully');
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
