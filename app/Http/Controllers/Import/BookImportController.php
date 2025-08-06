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

class BookImportController extends Controller
{
    public function index(){
        $showTable = false;
        return view('import.books.books', compact('showTable'));
    }
    public function upload(Request $request)
    {
        try{
            if($request->file('file') == null) return redirect()->route('import.import-books')->with('toast-warning', "Please select a file.");
            $showTable      = true;
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
        } catch(\Exception $e){
            $errors = "An error occurred while loading the books";
            return redirect()->route('import.import-books')->with('toast-error', $errors);
        }
        return view('import.books.books', compact('showTable', 'data'));
    }
    public function store(Request $request)
    {
        $data       = $request->input('data');
        $dataArray  = json_decode($data, true);
        $errors     = "";
        $newBooksCount = 0;
        $books = new Book();
        DB::beginTransaction();
        foreach ($dataArray as $item) {
            $item['book_type'] = strtolower($item['book_type']);
            $validator = Validator::make($item, [
                'accession'             => 'required|string|unique:' . Book::class . ',accession|max:50',
                'call_number'           => 'nullable|string|max:50',
                'title'                 => 'required|string|max:255',
                'authors'               => 'nullable|string|max:255',
                'book_type'             => 'nullable|string|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
                'description'           => 'nullable|string',
                'edition'               => 'nullable|string|max:50',
                'place_of_publication'  => 'nullable|string|max:100',
                'publisher'             => 'nullable|string|max:100',
                'copyrights'            => 'nullable|string|max:255',
                'category'              => 'required|string|in:' . implode(',', Category::pluck('name')->toArray()),
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
                    'barcode'               => $barcode->getBarcodeJPG($item['accession'], 'C39+', 2, 80, array(0, 0, 0, 0), false),
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
