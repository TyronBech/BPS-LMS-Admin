<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookCirculationController extends Controller
{
    public function index()
    {
        $barcode        = "";
        $title          = "";
        $availability   = $this->extract_enums('bk_books', 'availability_status');
        $data           = Book::select('accession', 'call_number', 'title', 'barcode', 'availability_status', 'condition_status')->get();
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability'));
    }
    public function search(Request $request)
    {
        $barcode        = $request->input('barcode');
        $title          = $request->input('title');
        $availability   = $request->input('availability');
        $validator = Validator::make($request->all(), [
            'availability'  => 'sometimes',
            'title'         => 'sometimes',
            'barcode'       => 'sometimes',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        $data = $this->generateData($request);
        $availability = $this->extract_enums('books', 'availability_status');
        if(!count($data)) return redirect()->route('report.book-circulation')->with('toast-error', 'No data found.');
        return view('report.book-circulations.book-circulations', compact('data', 'barcode', 'title', 'availability'));
    }
    private function generateData(Request $request)
    {
        $barcode        = $request->input('barcode');
        $title          = strtolower($request->input('title'));
        $availability   = $request->input('availability');
        $query          = Book::select('accession', 'call_number', 'title', 'barcode', 'availability_status', 'condition_status');
        if (strlen($barcode) > 0) {
            $query->where('barcode', 'like', '%' . $barcode . '%');
        }
        if (strlen($title) > 0) {
            $query->where(DB::raw('lower(title)'), 'like', '%' . $title . '%');
        }
        if (strlen($availability) > 0 && $availability != 'Choose availability status') {
            $query->where('availability_status', $availability);
        }
        $data = $query->get();
        return $data;
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
