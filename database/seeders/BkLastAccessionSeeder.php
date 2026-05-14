<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Book;
use App\Models\BkLastAccession;

class BkLastAccessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            $lastBook = Book::where('category_id', $category->id)
                ->orderBy('accession', 'desc')
                ->first();

            if ($lastBook) {
                BkLastAccession::updateOrCreate(
                    ['category_id' => $category->id],
                    ['accession_number' => $lastBook->accession]
                );
            }
        }
    }
}
