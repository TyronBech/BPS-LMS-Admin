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
        return view('inventory.inventory');
    }
    public function search(Request $request)
    {
        $data       = null;
        $conditions = $this->extract_enums('books', 'condition_status');
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        try{
            $barcode = $request->input('barcode');
            $data = Book::where('barcode', $barcode)->first();
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
                Inventory::create([
                    'book_id'             => $book->id,
                    'checked_at'          => now(),
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
