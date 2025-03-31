<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Category;

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
            } else if(count($rows[0]) > 11 || count($rows[0]) < 11){
                return redirect()->route('import.import-books')->with('toast-error', "An error occurred while saving book: Wrong number of columns.");
            }
            for($i = 1; $i < count($rows); $i++){
                $data[] = array(
                    'accession'             => $rows[$i][0],
                    'call_number'           => $rows[$i][1],
                    'title'                 => $rows[$i][2],
                    'authors'               => $rows[$i][3],
                    'edition'               => $rows[$i][4],
                    'place_of_publication'  => $rows[$i][5],
                    'publisher'             => $rows[$i][6],
                    'copyrights'            => $rows[$i][7],
                    'category'              => $rows[$i][8],
                    'barcode'               => $rows[$i][9],
                    'digital_copy_url'      => $rows[$i][10],
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
        foreach ($dataArray as $item) {
            DB::beginTransaction();
            try {
                $category = Category::select('id')->where('name', $item['category'])->first();
                if($category == null){
                    return redirect()->route('import.import-books')->with('toast-error', "An error occurred while saving book: Category not found.");
                }
                Book::create([
                    'accession'             => $item['accession'],
                    'call_number'           => $item['call_number'],
                    'title'                 => $item['title'],
                    'author'                => $item['authors'],
                    'edition'               => $item['edition'] ?? null,
                    'place_of_publication'  => $item['place_of_publication'],
                    'publisher'             => $item['publisher'],
                    'copyrights'            => $item['copyrights'],
                    'category_id'           => $category->id,
                    'barcode'               => $item['barcode'],
                    'digital_copy_url'      => $item['digital_copy_url'] ?? null,
                    'remarks'               => 'On Shelf',
                    'availability_status'   => 'Available',
                    'condition_status'      => 'New',
                    'created_at'            => now(),
                    'updated_at'            => now()
                ]);
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
            DB::commit();
        }
        return redirect()->route('import.import-books')->with('toast-success', 'Books imported successfully');
    }
}
