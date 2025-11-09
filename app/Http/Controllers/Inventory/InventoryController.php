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
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->savePageData($request);
        }

        $perPage    = $request->input('perPage', 10);
        $barcode    = $request->input('barcode');
        $conditions = $this->extract_enums('bk_books', 'condition_status');
        $remarks    = $this->extract_enums('bk_books', 'remarks');
        $inventory  = Inventory::with('book')
            ->where('checked_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                ]);
        return view('inventory.inventory', compact('inventory', 'conditions', 'remarks', 'perPage', 'barcode'));
    }
    public function search(Request $request)
    {
        $data       = null;
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', 'Please enter a barcode');
        }
        DB::beginTransaction();
        try {
            $barcode = $request->input('barcode');
            $data = Book::where('accession', $barcode)->first();
            if (!$data) {
                DB::rollBack();
                return redirect()->back()->with('toast-warning', 'Book not found!');
            }
            $isInInventory = Inventory::where('book_id', $data->id)->where('checked_at', null)->first();
            if ($isInInventory) {
                DB::rollBack();
                return redirect()->back()->with('toast-warning', 'Book already in inventory!');
            }
            Inventory::create([
                'book_id'             => $data->id,
                'checked_at'          => null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('inventory.dashboard')->with('toast-success', 'Book added to inventory successfully!');
    }
    public function update(Request $request)
    {
        $this->savePageData($request, true); // Final save, mark as checked
        return redirect()->route('inventory.dashboard')->with('toast-success', 'Inventory updated successfully!');
    }
    public function destroy(Request $request)
    {
        $accession = $request->input('accession');
        if (!$accession) {
            return redirect()->back()->with('toast-warning', 'Please select an inventory to delete');
        }
        DB::beginTransaction();
        try {
            $inventory = Inventory::with('book')->whereHas('book', function ($query) use ($accession) {
                $query->where('accession', $accession);
            })->where('checked_at', null)->first();
            if (!$inventory) {
                DB::rollBack();
                return redirect()->back()->with('toast-warning', 'No inventory found for this book!');
            }
            $inventory->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('inventory.dashboard')->with('toast-success', 'Inventory deleted successfully!');
    }

    private function savePageData(Request $request, $finalize = false)
    {
        $conditions = $request->input('condition', []);
        $remarks = $request->input('remarks', []);
        $accessions = array_keys($conditions + $remarks);

        if (empty($accessions)) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($accessions as $accession) {
                $book = Book::where('accession', $accession)->first();
                if (!$book) continue;

                $book->update([
                    'remarks'           => $remarks[$accession] ?? $book->remarks,
                    'condition_status'  => $conditions[$accession] ?? $book->condition_status,
                ]);

                if ($finalize) {
                    Inventory::where('book_id', $book->id)
                        ->whereNull('checked_at')
                        ->update(['checked_at' => now()]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
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
