<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            Log::info('Seeding book inventory from existing books...');
            // Backup all the inventory data
            Log::info('Backing up existing inventory data...');
            $inventory = Inventory::all();
            // Export it to a physical file (e.g., JSON or CSV)
            Log::info('Exporting inventory data to a file...');
            // check if the file path exists, if not create the directory
            $backupDir = storage_path('app/backups/Laravel');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            // check if the file exists, if it does, delete it
            $backupFilePath = $backupDir . '/inventory_backup.json';
            if (file_exists($backupFilePath)) {
                unlink($backupFilePath);
            }
            // Save the inventory data to the file in JSON format
            file_put_contents($backupFilePath, $inventory->toJson(JSON_PRETTY_PRINT));

            // Clear the inventory table
            Log::info('Clearing the inventory table...');
            Inventory::withTrashed()->forceDelete();

            // Insert all the books in the inventory table using the created_at as the checked_at timestamp
            Log::info('Inserting books into the inventory table...');
            $books = Book::select('id', 'created_at')->get();
            foreach ($books as $book) {
                Inventory::create([
                    'book_id'    => $book->id,
                    'checked_at' => $book->created_at,
                ]);
            }
            // Changing the remarks of all books into "Missing" except to the books which availability_status is "Borrowed"
            Log::info('Changing the remarks of all books into "Missing" except to the books which availability_status is "Borrowed"');
            Book::whereNot('availability_status', 'Available')->update(['remarks' => 'On Shelf']);
            Book::where('availability_status', 'Available')->update(['remarks' => 'Missing']);
            // Changing the availability status of books where the remarks are not on shelf
            Book::whereNot('remarks', 'On Shelf')->update(['availability_status' => 'Unavailable']);
            Log::info('Book inventory seeding completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error seeding book inventory: ' . $e->getMessage());
        }
        DB::commit();
    }
}
