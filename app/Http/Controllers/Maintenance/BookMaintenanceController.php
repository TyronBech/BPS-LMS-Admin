<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;

class BookMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $books = Book::with('category')
                    ->orderBy('accession', 'asc')
                    ->paginate($perPage)
                    ->appends([
                        'perPage' => $perPage,
                        'search' => $search,
                    ]);
        return view('maintenance.books.books', compact('books', 'perPage', 'search'));
    }
    public function create()
    {
        $books = new Book();
        $categories     = Category::all()->pluck('name', 'id');
        $condition      = $this->extract_enums($books->getTable(), 'condition_status');
        $availability   = $this->extract_enums($books->getTable(), 'availability_status');     
        $remarks        = $this->extract_enums($books->getTable(), 'remarks');
        $book_types     = $this->extract_enums($books->getTable(), 'book_type');
        return view('maintenance.books.create', compact('categories', 'condition', 'availability', 'remarks', 'book_types'));
    }
    public function store(Request $request)
    {
        $books = new Book();
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'nullable|string|max:50',
            'title'             => 'required|string|max:150',
            'authors'           => 'nullable|string|max:1024',
            'description'       => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'required|string|max:50',
            'publisher'         => 'required|string|max:100',
            'copyright'         => 'required|string|max:50',
            'cover_image'       => 'nullable',
            'digital_copy_url'  => 'nullable|string',
            'remarks'           => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:'.implode(',', Category::all()->pluck('id')->toArray()),
            'book_type'         => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(Book::where('accession', $request->input('accession'))->exists()){
            return redirect()->back()->with('toast-error', 'Book with this accession number already exists!');
        }
        if($request->hasFile('cover_image')){
            $image = $request->file('cover_image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['cover_image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $barcode = new DNS1D();
            Book::create([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number') ?? null,
                'barcode'               => $barcode->getBarcodeJPG($request->input('accession'), 'C39+', 2, 70, array(0, 0, 0, 0), true),
                'title'                 => $request->input('title'),
                'author'                => $request->input('authors') ?? null,
                'description'           => $request->input('description') ?? null,
                'edition'               => $request->input('edition') ?? null,
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'cover_image'           => $request->input('cover_image') ?? null,
                'digital_copy_url'      => $request->input('digital_copy_url') ?? null,
                'remarks'               => $request->input('remarks'),
                'category_id'           => $request->input('category'),
                'book_type'             => $request->input('book_type'),
                'condition_status'      => $request->input('condition'),
                'availability_status'   => $request->input('availability'),
                'created_at'            => now(),
                'updated_at'            => now()
            ]);
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            if($e->getCode() == 23000){
                return redirect()->back()->with('toast-error', 'Book with this accession number already exists!');
            } else {
                return redirect()->back()->with('toast-error', $e->getMessage());
            }
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Book added successfully');
    }
    public function edit(Request $request)
    {
        $book = null;
        try{
            $id = $request->input('id');
            $book = Book::findOrFail($id);
            $books = new Book();
            $categories     = Category::all()->pluck('name', 'id');
            $condition      = $this->extract_enums($books->getTable(), 'condition_status');
            $availability   = $this->extract_enums($books->getTable(), 'availability_status');     
            $remarks        = $this->extract_enums($books->getTable(), 'remarks');
            $book_types     = $this->extract_enums($books->getTable(), 'book_type');
        } catch(\Exception $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.books.edit', compact('book', 'categories', 'condition', 'availability', 'remarks', 'book_types'));
    }
    public function show(Request $request)
    {
        $search = strtolower($request->input('search'));
        $perPage = $request->input('perPage', 10);
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
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('legend', 'like', '%' . $search . '%');
            })
            ->orderBy('accession', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ])->withQueryString();
        return view('maintenance.books.books', compact('books', 'perPage', 'search'));
    }
    public function view(Request $request){
        $accession = $request->input('accession');
        $book = Book::with('category')->where('accession', $accession)->first();
        if(!$book){
            return redirect()->back()->with('toast-error', 'Book not found!');
        }
        return view('maintenance.books.view', compact('book'));
    }
    public function update(Request $request)
    {
        $books = new Book();
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'nullable|string|max:50',
            'title'             => 'required|string|max:150',
            'authors'           => 'nullable|string|max:1024',
            'description'       => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'required|string|max:50',
            'publisher'         => 'required|string|max:100',
            'copyright'         => 'required|string|max:50',
            'cover_image'       => 'nullable',
            'digital_copy_url'  => 'nullable|string',
            'remarks'           => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:'.implode(',', Category::all()->pluck('id')->toArray()),
            'book_type'         => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:'.implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        //dd($request->all());
        if($request->hasFile('cover_image')){
            $image = $request->file('cover_image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['cover_image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $barcode = new DNS1D();
            $book = Book::findOrFail($request->input('id'));
            $book->update([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number'),
                'barcode'               => $barcode->getBarcodeJPG($request->input('accession'), 'C39+', 2, 70, array(0, 0, 0, 0), true),
                'title'                 => $request->input('title'),
                'author'                => $request->input('authors'),
                'description'           => $request->input('description'),
                'edition'               => $request->input('edition'),
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'remarks'               => $request->input('remarks'),
                'category_id'           => $request->input('category'),
                'book_type'             => $request->input('book_type'),
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
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $id = $request->input('id');
            Book::find($id)->delete();
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('maintenance.books')->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.books')->with('toast-success', 'Book deleted successfully');
    }
    public function bulkDelete(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('ids')), function($id) {
            return is_numeric($id) && $id > 0;
        });
        if(empty($ids)){
            return redirect()->back()->with('toast-warning', 'No books selected for deletion!');
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            Book::whereIn('id', $ids)->delete();
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.books')->with('toast-success', 'Books deleted successfully');
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
