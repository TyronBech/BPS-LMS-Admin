<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetBookMatrix extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        DB::beginTransaction();
        try{
            Log::info('Resetting book matrix...');
            foreach ($categories as $category) {
                $total = Book::withTrashed()
                    ->where('category_id', $category->id)
                    ->count();
    
                $discarded = Book::onlyTrashed()
                    ->where('category_id', $category->id)
                    ->count();
    
                $present = max(0, $total - $discarded);
    
                $category->update([
                    'previous_inventory' => $total,
                    'newly_acquired' => 0,
                    'discarded' => $discarded,
                    'present_inventory' => $present,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reset book matrix: ' . $e->getMessage());
        }
        DB::commit();
        Log::info('Book matrix has been reset successfully.');
    }
}
