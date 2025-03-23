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
        $inventory      = Inventory::with('book')->get();
        $remarks   = $this->extract_enums('books', 'remarks');
        $conditions     = $this->extract_enums('books', 'condition_status');
        return view('inventory.inventory', compact('inventory', 'remarks', 'conditions'));
    }
    public function search(Request $request)
    {
        $data       = null;
        $remarks    = $this->extract_enums('books', 'remarks');
        $conditions = $this->extract_enums('books', 'condition_status');
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $barcode = $request->input('barcode');
            $data = Book::with('inventory')->where('barcode', $barcode)->first();
        } catch (\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('inventory.inventory')->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return response()->json(['data' => $data, 'remarks' => $remarks, 'conditions' => $conditions]);
    }
    public function update(Request $request)
    {
        $remarks = $request->input('remarks');
        $condition    = $request->input('condition');
        DB::beginTransaction();
        try{
            foreach($remarks as $key => $value){
                $cond = $condition[$key];
                $book = Book::where('accession', $key)->first();
                Inventory::create([
                    'book_id'             => $book->id,
                    'checked_at'          => now(),
                ]);
                $book->update([
                    'remarks'           => $value,
                    'condition_status'  => $cond,
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
