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
        $inventory = Inventory::with('book')->get();
        return view('inventory.inventory', compact('inventory'));
    }
    public function search(Request $request)
    {
        $data       = null;
        $response   = null;
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $barcode = $request->input('barcode');
            $book = Book::where('barcode', $barcode)->first();
            if($book){
                $response = Inventory::create([
                    'book_id'       => $book->id,
                    'checked_at'    => now(),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('inventory.inventory')->with('toast-error', $e->getMessage());
        }
        DB::commit();
        if($response){
            $data = Inventory::with('book')->find($response->id);
        }
        return $data;
    }
}
