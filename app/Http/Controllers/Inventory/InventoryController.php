<?php

namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Inventory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(){
        $conditions = $this->extract_enums('bk_books', 'condition_status');
        $books = Book::with('inventory')
            ->whereHas('inventory', function($query){
                $query->where('checked_at', null);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        return view('inventory.inventory', compact('books', 'conditions'));
    }
    public function search(Request $request)
    {
        $data       = null;
        $conditions = $this->extract_enums('bk_books', 'condition_status');
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        try{
            $barcode = $request->input('barcode');
            $data = Book::where('accession', $barcode)->first();
            if (!$data) {
                return redirect()->back()->with('toast-warning', 'No book found with this barcode!');
            }
            Inventory::create([
                'book_id'             => $data->id,
                'checked_at'          => null,
            ]);
        } catch (\Illuminate\Database\QueryException $e){
            return redirect()->route('inventory.inventory')->with('toast-error', $e->getMessage());
        }
        return response()->json(['data' => $data, 'conditions' => $conditions]);
    }
    public function update(Request $request)
    {
        $condition = $request->input('condition');
        DB::beginTransaction();
        try{
            foreach($condition as $key => $value){
                $book = Book::where('accession', $key)->first();
                $inventory = Inventory::where('book_id', $book->id)->where('checked_at', null)->first();
                if (!$inventory) {
                    return redirect()->back()->with('toast-warning', 'No inventory found for this book!');
                }
                $inventory->update([
                    'checked_at' => now(),
                ]);
                $book->update([
                    'remarks'           => "On Shelf",
                    'condition_status'  => $value,
                ]);
            }
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('inventory.inventory')->with('toast-success', 'Inventory updated successfully!');
    }
    private function extract_enums($table, $columnName)
    {
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
