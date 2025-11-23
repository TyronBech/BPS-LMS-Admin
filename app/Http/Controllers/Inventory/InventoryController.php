<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Inventory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Inventory: Index page accessed', [
            'user_id' => auth()->guard('admin')->id(),
            'user_name' => auth()->guard('admin')->user()->full_name,
            'user_email' => auth()->guard('admin')->user()->email,
            'ip_address' => $request->ip(),
            'request_method' => $request->method(),
            'timestamp' => now(),
        ]);

        if ($request->isMethod('post')) {
            Log::debug('Inventory: Processing POST request to save page data', [
                'user_id' => auth()->guard('admin')->id(),
                'has_conditions' => $request->has('condition'),
                'has_remarks' => $request->has('remarks'),
            ]);

            $this->savePageData($request);
        }

        $perPage    = $request->input('perPage', 10);
        $barcode    = $request->input('barcode');
        $conditions = $this->extract_enums('bk_books', 'condition_status');
        $remarks    = $this->extract_enums('bk_books', 'remarks');

        Log::debug('Inventory: Fetching inventory data', [
            'per_page' => $perPage,
            'barcode_search' => $barcode,
            'user_id' => auth()->guard('admin')->id(),
        ]);

        $inventory  = Inventory::with('book')
            ->where('checked_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                ]);

        Log::info('Inventory: Page loaded successfully', [
            'total_items' => $inventory->total(),
            'displayed_items' => $inventory->count(),
            'current_page' => $inventory->currentPage(),
            'per_page' => $perPage,
            'user_id' => auth()->guard('admin')->id(),
            'timestamp' => now(),
        ]);

        return view('inventory.inventory', compact('inventory', 'conditions', 'remarks', 'perPage', 'barcode'));
    }

    public function search(Request $request)
    {
        Log::info('Inventory: Search initiated', [
            'user_id' => auth()->guard('admin')->id(),
            'user_name' => auth()->guard('admin')->user()->full_name,
            'barcode' => $request->input('barcode'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $data       = null;
        $validator  = Validator::make($request->all(), [
            'barcode' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Inventory: Search validation failed - No barcode provided', [
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-warning', 'Please enter a barcode');
        }

        DB::beginTransaction();
        Log::debug('Inventory: Database transaction started', [
            'user_id' => auth()->guard('admin')->id(),
        ]);

        try {
            $barcode = $request->input('barcode');

            Log::debug('Inventory: Searching for book', [
                'barcode' => $barcode,
                'user_id' => auth()->guard('admin')->id(),
            ]);

            $data = Book::where('accession', $barcode)->first();

            if (!$data) {
                DB::rollBack();

                Log::warning('Inventory: Book not found', [
                    'barcode' => $barcode,
                    'user_id' => auth()->guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                return redirect()->back()->with('toast-warning', 'Book not found!');
            }

            Log::debug('Inventory: Book found, checking if already in inventory', [
                'book_id' => $data->id,
                'book_title' => $data->title,
                'accession' => $data->accession,
                'user_id' => auth()->guard('admin')->id(),
            ]);

            $isInInventory = Inventory::where('book_id', $data->id)->where('checked_at', null)->first();

            if ($isInInventory) {
                DB::rollBack();

                Log::warning('Inventory: Book already in inventory', [
                    'book_id' => $data->id,
                    'book_title' => $data->title,
                    'accession' => $data->accession,
                    'inventory_id' => $isInInventory->id,
                    'user_id' => auth()->guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                return redirect()->back()->with('toast-warning', 'Book already in inventory!');
            }

            Log::info('Inventory: Adding book to inventory', [
                'book_id' => $data->id,
                'book_title' => $data->title,
                'accession' => $data->accession,
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            $inventory = Inventory::create([
                'book_id'    => $data->id,
                'checked_at' => null,
            ]);

            Log::info('Inventory: Book added to inventory successfully', [
                'inventory_id' => $inventory->id,
                'book_id' => $data->id,
                'book_title' => $data->title,
                'accession' => $data->accession,
                'added_by' => auth()->guard('admin')->id(),
                'added_by_name' => auth()->guard('admin')->user()->full_name,
                'timestamp' => now(),
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            Log::error('Inventory: Database error while adding book to inventory', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? 'N/A',
                'barcode' => $barcode,
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }

        DB::commit();
        Log::debug('Inventory: Database transaction committed', [
            'user_id' => auth()->guard('admin')->id(),
        ]);

        return redirect()->route('inventory.dashboard')->with('toast-success', 'Book added to inventory successfully!');
    }

    public function update(Request $request)
    {
        Log::info('Inventory: Update initiated', [
            'user_id' => auth()->guard('admin')->id(),
            'user_name' => auth()->guard('admin')->user()->full_name,
            'user_email' => auth()->guard('admin')->user()->email,
            'ip_address' => $request->ip(),
            'has_conditions' => $request->has('condition'),
            'has_remarks' => $request->has('remarks'),
            'timestamp' => now(),
        ]);

        try {
            $this->savePageData($request, true); // Final save, mark as checked

            Log::info('Inventory: Update completed successfully', [
                'user_id' => auth()->guard('admin')->id(),
                'user_name' => auth()->guard('admin')->user()->full_name,
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-success', 'Inventory updated successfully!');

        } catch (\Exception $e) {
            Log::error('Inventory: Update failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Failed to update inventory: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        $accession = $request->input('accession');

        Log::info('Inventory: Delete initiated', [
            'user_id' => auth()->guard('admin')->id(),
            'user_name' => auth()->guard('admin')->user()->full_name,
            'accession' => $accession,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if (!$accession) {
            Log::warning('Inventory: Delete failed - No accession provided', [
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-warning', 'Please select an inventory to delete');
        }

        DB::beginTransaction();
        Log::debug('Inventory: Database transaction started for deletion', [
            'accession' => $accession,
            'user_id' => auth()->guard('admin')->id(),
        ]);

        try {
            Log::debug('Inventory: Searching for inventory record', [
                'accession' => $accession,
                'user_id' => auth()->guard('admin')->id(),
            ]);

            $inventory = Inventory::with('book')->whereHas('book', function ($query) use ($accession) {
                $query->where('accession', $accession);
            })->where('checked_at', null)->first();

            if (!$inventory) {
                DB::rollBack();

                Log::warning('Inventory: No inventory found for deletion', [
                    'accession' => $accession,
                    'user_id' => auth()->guard('admin')->id(),
                    'timestamp' => now(),
                ]);

                return redirect()->back()->with('toast-warning', 'No inventory found for this book!');
            }

            $inventoryData = [
                'inventory_id' => $inventory->id,
                'book_id' => $inventory->book_id,
                'book_title' => $inventory->book->title,
                'accession' => $inventory->book->accession,
                'created_at' => $inventory->created_at,
            ];

            $inventory->delete();

            Log::info('Inventory: Record deleted successfully', [
                'deleted_inventory' => $inventoryData,
                'deleted_by' => auth()->guard('admin')->id(),
                'deleted_by_name' => auth()->guard('admin')->user()->full_name,
                'timestamp' => now(),
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            Log::error('Inventory: Database error during deletion', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? 'N/A',
                'accession' => $accession,
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }

        DB::commit();
        Log::debug('Inventory: Database transaction committed for deletion', [
            'user_id' => auth()->guard('admin')->id(),
        ]);

        return redirect()->route('inventory.dashboard')->with('toast-success', 'Inventory deleted successfully!');
    }

    private function savePageData(Request $request, $finalize = false)
    {
        $conditions = $request->input('condition', []);
        $remarks = $request->input('remarks', []);
        $accessions = array_keys($conditions + $remarks);

        Log::debug('Inventory: Saving page data', [
            'finalize' => $finalize,
            'conditions_count' => count($conditions),
            'remarks_count' => count($remarks),
            'total_accessions' => count($accessions),
            'user_id' => auth()->guard('admin')->id(),
        ]);

        if (empty($accessions)) {
            Log::debug('Inventory: No data to save, accessions array is empty', [
                'user_id' => auth()->guard('admin')->id(),
            ]);
            return;
        }

        DB::beginTransaction();
        Log::debug('Inventory: Database transaction started for saving page data', [
            'user_id' => auth()->guard('admin')->id(),
        ]);

        try {
            $updatedCount = 0;
            $checkedCount = 0;

            foreach ($accessions as $accession) {
                $book = Book::where('accession', $accession)->first();
                if (!$book) {
                    Log::warning('Inventory: Book not found during page data save', [
                        'accession' => $accession,
                        'user_id' => auth()->guard('admin')->id(),
                    ]);
                    continue;
                }

                $oldCondition = $book->condition_status;
                $oldRemarks = $book->remarks;
                $newCondition = $conditions[$accession] ?? $book->condition_status;
                $newRemarks = $remarks[$accession] ?? $book->remarks;

                $book->update([
                    'remarks'          => $newRemarks,
                    'condition_status' => $newCondition,
                ]);

                if ($oldCondition !== $newCondition || $oldRemarks !== $newRemarks) {
                    Log::info('Inventory: Book status updated', [
                        'book_id' => $book->id,
                        'book_title' => $book->title,
                        'accession' => $accession,
                        'old_condition' => $oldCondition,
                        'new_condition' => $newCondition,
                        'old_remarks' => $oldRemarks,
                        'new_remarks' => $newRemarks,
                        'updated_by' => auth()->guard('admin')->id(),
                        'updated_by_name' => auth()->guard('admin')->user()->full_name,
                        'timestamp' => now(),
                    ]);
                    $updatedCount++;
                }

                if ($finalize) {
                    $updated = Inventory::where('book_id', $book->id)
                        ->whereNull('checked_at')
                        ->update(['checked_at' => now()]);

                    if ($updated > 0) {
                        Log::info('Inventory: Book marked as checked', [
                            'book_id' => $book->id,
                            'book_title' => $book->title,
                            'accession' => $accession,
                            'checked_at' => now(),
                            'checked_by' => auth()->guard('admin')->id(),
                            'checked_by_name' => auth()->guard('admin')->user()->full_name,
                            'timestamp' => now(),
                        ]);
                        $checkedCount++;
                    }
                }
            }

            Log::info('Inventory: Page data saved successfully', [
                'finalize' => $finalize,
                'total_accessions' => count($accessions),
                'books_updated' => $updatedCount,
                'books_checked' => $checkedCount,
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            Log::error('Inventory: Database error during page data save', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? 'N/A',
                'finalize' => $finalize,
                'user_id' => auth()->guard('admin')->id(),
                'timestamp' => now(),
            ]);

            throw $e;
        }

        DB::commit();
        Log::debug('Inventory: Database transaction committed for page data save', [
            'user_id' => auth()->guard('admin')->id(),
        ]);
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
