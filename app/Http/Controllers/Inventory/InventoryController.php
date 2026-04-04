<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ArchiveInventory;
use App\Models\Book;
use App\Models\Inventory;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    private const INVENTORY_ACTIVE_KEY = 'inventory_cycle_active';

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $inventoryActive = $this->isInventoryActive();
        $conditions = $this->extract_enums('bk_books', 'condition_status');
        $remarks = $this->extract_enums('bk_books', 'remarks');
        $stats = $this->getInventoryStats();

        Log::info('Inventory: Dashboard accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'inventory_active' => $inventoryActive,
            'per_page' => $perPage,
            'timestamp' => now(),
        ]);

        $inventory = Inventory::with('book')
            ->whereHas('book')
            ->when(
                $inventoryActive,
                fn($query) => $query->where('is_scanned', true),
                fn($query) => $query->whereNotNull('checked_at')
            )
            ->orderByRaw('CASE WHEN checked_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
            ]);

        return view('inventory.inventory', compact('inventory', 'conditions', 'remarks', 'perPage', 'inventoryActive', 'stats'));
    }

    public function start(Request $request)
    {
        if ($this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'Inventory is already in progress.');
        }

        try {
            $archivedCount = 0;
            $seededCount = 0;

            DB::transaction(function () use (&$archivedCount, &$seededCount) {
                $archivedCount = $this->archiveCurrentInventory();
                Inventory::withTrashed()->forceDelete();
                $seededCount = $this->seedInventoryBooks();
                $this->setInventoryActive(true);
            });

            Log::info('Inventory: Cycle started', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'archived_count' => $archivedCount,
                'seeded_count' => $seededCount,
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-success', 'Inventory started. Scan books and click save to timestamp them.');
        } catch (\Throwable $e) {
            Log::error('Inventory: Failed to start cycle', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-error', 'Failed to start inventory.');
        }
    }

    public function search(Request $request)
    {
        if (!$this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'Start the inventory first before scanning books.');
        }

        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', 'Please enter a barcode');
        }

        $barcode = trim((string) $request->input('barcode'));

        try {
            DB::transaction(function () use ($barcode) {
                $book = Book::where('accession', $barcode)->first();

                if (!$book) {
                    throw new \RuntimeException('Book not found.');
                }

                $inventory = Inventory::firstOrCreate(
                    ['book_id' => $book->id],
                    [
                        'is_scanned' => 0,
                        'checked_at' => null,
                    ]
                );

                if ($inventory->is_scanned) {
                    throw new \RuntimeException($inventory->checked_at ? 'Book already scanned and saved.' : 'Book already scanned.');
                }

                $inventory->update([
                    'is_scanned' => 1,
                    'checked_at' => null,
                ]);
            });

            Log::info('Inventory: Book scanned', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'barcode' => $barcode,
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard', ['perPage' => $request->input('perPage', 10)])->with('toast-success', 'Book scanned. Click save to timestamp it.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('toast-warning', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Inventory: Scan failed', [
                'user_id' => Auth::guard('admin')->id(),
                'barcode' => $barcode,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Failed to scan book.');
        }
    }

    public function update(Request $request)
    {
        if (!$this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'Start the inventory first before saving scans.');
        }

        $validator = $this->validateSelections($request);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        try {
            $updatedCount = 0;
            $savedCount = 0;

            DB::transaction(function () use ($request, &$updatedCount, &$savedCount) {
                $updatedCount = $this->syncBookSelections($request);
                $savedCount = Inventory::where('is_scanned', true)
                    ->whereNull('checked_at')
                    ->update([
                        'checked_at' => now(),
                        'updated_at' => now(),
                    ]);
            });

            Log::info('Inventory: Scanned books saved', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'updated_books' => $updatedCount,
                'saved_scans' => $savedCount,
                'timestamp' => now(),
            ]);

            if ($updatedCount === 0 && $savedCount === 0) {
                return redirect()->route('inventory.dashboard', ['perPage' => $request->input('perPage', 10)])->with('toast-warning', 'No scanned books were ready to save.');
            }

            return redirect()->route('inventory.dashboard', ['perPage' => $request->input('perPage', 10)])->with('toast-success', 'Scanned books saved successfully.');
        } catch (\Throwable $e) {
            Log::error('Inventory: Save failed', [
                'error_message' => $e->getMessage(),
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Failed to save scanned books.');
        }
    }

    public function finish(Request $request)
    {
        if (!$this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'There is no active inventory to finish.');
        }

        $pendingSaveCount = Inventory::where('is_scanned', true)
            ->whereNull('checked_at')
            ->count();

        try {
            $finalizedCount = 0;
            $missingCount = 0;

            DB::transaction(function () use (&$finalizedCount, &$missingCount) {
                $finalizedCount = Book::whereIn('id', Inventory::whereNotNull('checked_at')->select('book_id'))
                    ->update([
                        'availability_status' => DB::raw("CASE WHEN remarks = 'On Shelf' THEN 'Available' ELSE 'Unavailable' END"),
                    ]);

                $missingCount = Book::whereIn('id', Inventory::whereNull('checked_at')->select('book_id'))
                    ->where('remarks', 'On Shelf')
                    ->update([
                        'remarks' => 'Missing',
                        'availability_status' => 'Unavailable',
                    ]);

                $this->setInventoryActive(false);
            });

            Log::info('Inventory: Cycle finished', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'finalized_count' => $finalizedCount,
                'missing_count' => $missingCount,
                'pending_save_count' => $pendingSaveCount,
                'timestamp' => now(),
            ]);

            $message = "Inventory finished successfully. {$finalizedCount} saved books kept their selected remarks and {$missingCount} unsaved On Shelf books were marked Missing.";
            if ($pendingSaveCount > 0) {
                $message .= " {$pendingSaveCount} scanned books were not saved and were not counted.";
            }

            return redirect()->route('inventory.dashboard')->with('toast-success', $message);
        } catch (\Throwable $e) {
            Log::error('Inventory: Finish failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-error', 'Failed to finish inventory.');
        }
    }

    public function cancel(Request $request)
    {
        if (!$this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'There is no active inventory to cancel.');
        }

        try {
            $restoredCount = 0;

            DB::transaction(function () use (&$restoredCount) {
                $restoredCount = $this->restoreLatestArchivedInventory();

                if ($restoredCount === 0) {
                    throw new \RuntimeException('No archived inventory snapshot found to restore.');
                }

                $this->setInventoryActive(false);
            });

            Log::info('Inventory: Cycle cancelled and previous snapshot restored', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'restored_count' => $restoredCount,
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-success', "Inventory cancelled. Previous snapshot restored ({$restoredCount} books).");
        } catch (\RuntimeException $e) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Inventory: Cancel failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard')->with('toast-error', 'Failed to cancel inventory.');
        }
    }

    public function destroy(Request $request)
    {
        if (!$this->isInventoryActive()) {
            return redirect()->route('inventory.dashboard')->with('toast-warning', 'There is no active inventory to modify.');
        }

        $validator = Validator::make($request->all(), [
            'accession' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', 'Please select a scanned book to reset.');
        }

        $accession = $request->input('accession');

        try {
            $inventory = Inventory::with('book')
                ->whereHas('book', function ($query) use ($accession) {
                    $query->where('accession', $accession);
                })
                ->first();

            if (!$inventory) {
                return redirect()->back()->with('toast-warning', 'No inventory record found for this book.');
            }

            $inventory->update([
                'is_scanned' => 0,
                'checked_at' => null,
            ]);

            Log::info('Inventory: Scan reset', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'accession' => $accession,
                'timestamp' => now(),
            ]);

            return redirect()->route('inventory.dashboard', ['perPage' => $request->input('perPage', 10)])->with('toast-success', 'Scanned book reset successfully.');
        } catch (\Throwable $e) {
            Log::error('Inventory: Reset failed', [
                'accession' => $accession,
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Failed to reset scanned book.');
        }
    }

    private function archiveCurrentInventory(): int
    {
        $records = Inventory::with('book')
            ->whereHas('book')
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $archivedAt = now();
        $payload = $records->map(function (Inventory $inventory) use ($archivedAt) {
            return [
                'book_id' => $inventory->book_id,
                'accession' => $inventory->book->accession,
                'call_number' => $inventory->book->call_number,
                'title' => $inventory->book->title,
                'author' => $inventory->book->author,
                'remarks' => $inventory->book->remarks,
                'checked_at' => $inventory->checked_at,
                'archived_at' => $archivedAt,
            ];
        })->all();

        foreach (array_chunk($payload, 500) as $chunk) {
            ArchiveInventory::insert($chunk);
        }

        return count($payload);
    }

    private function seedInventoryBooks(): int
    {
        $seededCount = 0;

        Book::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($books) use (&$seededCount) {
                $timestamp = now();
                $payload = [];

                foreach ($books as $book) {
                    $payload[] = [
                        'book_id' => $book->id,
                        'is_scanned' => 0,
                        'checked_at' => null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                if (!empty($payload)) {
                    Inventory::insert($payload);
                    $seededCount += count($payload);
                }
            });

        return $seededCount;
    }

    private function restoreLatestArchivedInventory(): int
    {
        $latestArchivedAt = ArchiveInventory::query()->max('archived_at');

        if (!$latestArchivedAt) {
            return 0;
        }

        $records = ArchiveInventory::query()
            ->where('archived_at', $latestArchivedAt)
            ->orderBy('id')
            ->get(['book_id', 'remarks', 'checked_at']);

        if ($records->isEmpty()) {
            return 0;
        }

        $bookIds = $records->pluck('book_id')->unique()->values()->all();
        $existingBookIds = Book::query()
            ->whereIn('id', $bookIds)
            ->pluck('id')
            ->all();

        $existingBookLookup = array_fill_keys($existingBookIds, true);
        $timestamp = now();
        $payload = [];
        $bookRollbackPayload = [];

        foreach ($records as $record) {
            if (!isset($existingBookLookup[$record->book_id])) {
                continue;
            }

            $payload[] = [
                'book_id' => $record->book_id,
                'is_scanned' => $record->checked_at !== null,
                'checked_at' => $record->checked_at,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if ($record->remarks !== null) {
                $bookRollbackPayload[] = [
                    'id' => $record->book_id,
                    'remarks' => $record->remarks,
                    'availability_status' => $record->remarks === 'On Shelf' ? 'Available' : 'Unavailable',
                    'updated_at' => $timestamp,
                ];
            }
        }

        if (empty($payload)) {
            return 0;
        }

        Inventory::withTrashed()->forceDelete();

        foreach (array_chunk($payload, 500) as $chunk) {
            Inventory::insert($chunk);
        }

        if (!empty($bookRollbackPayload)) {
            foreach ($bookRollbackPayload as $bookRollback) {
                Book::query()
                    ->where('id', $bookRollback['id'])
                    ->update([
                        'remarks' => $bookRollback['remarks'],
                        'availability_status' => $bookRollback['availability_status'],
                        'updated_at' => $bookRollback['updated_at'],
                    ]);
            }
        }

        return count($payload);
    }

    private function validateSelections(Request $request)
    {
        return Validator::make($request->all(), [
            'condition' => 'nullable|array',
            'condition.*' => 'nullable|in:' . implode(',', $this->extract_enums('bk_books', 'condition_status')),
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|in:' . implode(',', $this->extract_enums('bk_books', 'remarks')),
        ]);
    }

    private function syncBookSelections(Request $request): int
    {
        $conditions = $request->input('condition', []);
        $remarks = $request->input('remarks', []);
        $bookIds = array_unique(array_merge(array_keys($conditions), array_keys($remarks)));
        $updatedCount = 0;

        foreach ($bookIds as $bookId) {
            $inventory = Inventory::with('book')
                ->where('is_scanned', true)
                ->where('book_id', (int) $bookId)
                ->first();

            if (!$inventory || !$inventory->book) {
                continue;
            }

            $book = $inventory->book;
            $newCondition = $conditions[$bookId] ?? $book->condition_status;
            $newRemarks = $remarks[$bookId] ?? $book->remarks;
            $newAvailability = $newRemarks === 'On Shelf' ? 'Available' : 'Unavailable';

            if (
                $book->condition_status !== $newCondition ||
                $book->remarks !== $newRemarks ||
                $book->availability_status !== $newAvailability
            ) {
                $book->update([
                    'condition_status' => $newCondition,
                    'remarks' => $newRemarks,
                    'availability_status' => $newAvailability,
                ]);

                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    private function getInventoryStats(): array
    {
        return [
            'total_books' => Book::count(),
            'scanned' => Inventory::where('is_scanned', true)->count(),
            'saved' => Inventory::whereNotNull('checked_at')->count(),
            'pending_save' => Inventory::where('is_scanned', true)->whereNull('checked_at')->count(),
        ];
    }

    private function isInventoryActive(): bool
    {
        $value = SystemSetting::where('key', self::INVENTORY_ACTIVE_KEY)->value('value');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function setInventoryActive(bool $active): void
    {
        SystemSetting::updateOrCreate(
            ['key' => self::INVENTORY_ACTIVE_KEY],
            [
                'value' => $active ? '1' : '0',
                'description' => 'Tracks whether the book inventory cycle is currently active.',
            ]
        );
    }

    private function extract_enums($table, $columnName)
    {
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;

        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];

        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }

        return $enumValues;
    }
}
