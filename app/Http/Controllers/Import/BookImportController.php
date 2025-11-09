<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Category;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class BookImportController extends Controller
{
    public function index(Request $request){
        if (!$request->has('page') && !$request->has('perPage')) {
            $request->session()->forget('book_import_data');
        }
        $showTable = false;
        return view('import.books.books', compact('showTable'));
    }
    public function upload(Request $request)
    {
        
        try{
            $sessionData = $request->session()->get('book_import_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
                // Merge submitted edits into the session data
                $submittedBooks = $request->input('books', []);
                foreach ($submittedBooks as $index => $book) {
                    if (isset($sessionData[$index])) {
                        $sessionData[$index] = array_merge($sessionData[$index], $book);
                    }
                }
                $request->session()->put('book_import_data', $sessionData);
                $data = $sessionData;
            } else if ($request->has('page') || $request->has('perPage')) {
                // Navigating via GET, just use session data
                $data = $sessionData;
            } else {
                // Initial file upload
                if($request->file('file') == null) return redirect()->route('import.import-books')->with('toast-warning', "Please select a file.");
                $file           = $request->file('file');
                $reader         = new ReaderXlsx();
                $spreadsheet    = $reader->load($file);
                $sheet          = $spreadsheet->getActiveSheet();
                $rows           = $sheet->toArray();
                $data           = array();
                if($rows[0][0] == null){
                    return redirect()->route('import.import-books')->with('toast-error', "Excel file is empty.");
                }
                for($i = 19; $i < count($rows); $i++){
                    if($rows[$i][1] == null &&
                        $rows[$i][2] == null &&
                        $rows[$i][3] == null &&
                        $rows[$i][4] == null &&
                        $rows[$i][5] == null &&
                        $rows[$i][6] == null &&
                        $rows[$i][7] == null &&
                        $rows[$i][8] == null &&
                        $rows[$i][9] == null &&
                        $rows[$i][10] == null &&
                        $rows[$i][11] == null &&
                        $rows[$i][12] == null) continue;
                    $data[] = array(
                        'accession'             => $rows[$i][1],
                        'call_number'           => $rows[$i][2],
                        'title'                 => $rows[$i][3],
                        'authors'               => $rows[$i][4],
                        'book_type'             => $rows[$i][5],
                        'description'           => $rows[$i][6],
                        'edition'               => $rows[$i][7],
                        'place_of_publication'  => $rows[$i][8],
                        'publisher'             => $rows[$i][9],
                        'copyrights'            => $rows[$i][10],
                        'category'              => $rows[$i][11],
                        'digital_copy_url'      => $rows[$i][12],
                    );
                }
                $request->session()->put('book_import_data', $data);
            }

            $showTable = true;
            // Paginate the data array
            $perPage = $request->input('perPage', 10);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
            $paginatedData = new LengthAwarePaginator($currentItems, count($data), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        } catch(\Exception $e){
            $errors = "An error occurred while loading the books: " . $e->getMessage();
            return redirect()->route('import.import-books')->with('toast-error', $errors);
        }
        return view('import.books.books', ['showTable' => $showTable, 'data' => $paginatedData, 'perPage' => $perPage]);
    }
    public function store(Request $request)
    {
        $submittedBooks = $request->input('books', []);
        $allBooks = $request->session()->get('book_import_data', []);

        // Update the session data with the submitted (potentially edited) data
        foreach ($submittedBooks as $index => $book) {
            if (isset($allBooks[$index])) {
                $allBooks[$index] = $book;
            }
        }
        
        $data       = $allBooks;
        $errors     = "";
        $newBooksCount = 0;
        $books = new Book();
        DB::beginTransaction();
        foreach ($data as $item) {
            $item['book_type'] = strtolower($item['book_type']);
            $item['category'] = strtolower($item['category']);
            $validator = Validator::make($item, [
                'accession'             => 'required|string|max:50',
                'call_number'           => 'nullable|string|max:50',
                'title'                 => 'required|string|max:255',
                'authors'               => 'nullable|string|max:255',
                'book_type'             => 'nullable|string|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
                'description'           => 'nullable|string',
                'edition'               => 'nullable|string|max:50',
                'place_of_publication'  => 'nullable|string|max:100',
                'publisher'             => 'nullable|string|max:100',
                'copyrights'            => 'nullable|string|max:255',
                'category'              => 'required|string|in:' . implode(',', Category::pluck(DB::raw('lower(name)'))->toArray()),
                'digital_copy_url'      => 'nullable|url',
            ]);
            if($validator->fails()){
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for accession: ' . $item['accession'];
                return redirect()->route('import.import-books')->with('toast-error', $errors);
            }
            try {
                $category = Category::select('id')->where(DB::raw('lower(name)'), strtolower($item['category']))->first();
                if($category == null){
                    DB::rollBack();
                    return redirect()->route('import.import-books')->with('toast-warning', 'Category not found: ' . $item['category']);
                }
                if(Book::where('accession', $item['accession'])->exists()){
                    DB::rollBack();
                    return redirect()->route('import.import-books')->with('toast-warning', 'Duplicate accession number found: ' . $item['accession']);
                }
                $barcode = new DNS1D();
                Book::create([
                    'accession'             => $item['accession'],
                    'call_number'           => $item['call_number'] ?? null,
                    'title'                 => $item['title'],
                    'author'                => $item['authors'] ?? null,
                    'book_type'             => $item['book_type'] ?? null,
                    'description'           => $item['description'] ?? null,
                    'edition'               => $item['edition'] ?? null,
                    'place_of_publication'  => $item['place_of_publication'],
                    'publisher'             => $item['publisher'],
                    'copyrights'            => $item['copyrights'],
                    'category_id'           => $category->id,
                    'barcode'               => $barcode->getBarcodeJPG($item['accession'], 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'digital_copy_url'      => $item['digital_copy_url'] ?? null,
                    'remarks'               => 'On Shelf',
                    'availability_status'   => 'Available',
                    'condition_status'      => 'New',
                ]);
                $newBooksCount++;
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                if ($e->getCode() == 23000) {
                    $errors = "Duplicate ID found for Book: " . $item['first_name'] . " " . $item['last_name'];
                } else if ($e->getCode() == "HY000") {
                    $errors = $e->getMessage();
                } else {
                    $errors = "An error occurred while saving Book: " . $e->getMessage();
                }
                return redirect()->route('import.import-books')->with('toast-error', $errors);
            }
        }
        DB::commit();
        $request->session()->forget('book_import_data');
        return redirect()->route('import.import-books')->with('toast-success', 'Books imported successfully: ' . $newBooksCount . ' new books added.');
    }
    public function downloadTemplate()
    {
        $filePath = public_path('excel/Book-template.xlsx');

        if (File::exists($filePath)) {
            return Response::download($filePath, 'Book-template.xlsx');
        }
        abort(404, 'File not found.');
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
