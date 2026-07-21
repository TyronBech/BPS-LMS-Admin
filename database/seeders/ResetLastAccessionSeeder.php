<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Book;
use App\Models\BkLastAccession;

class ResetLastAccessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            $accessions = Book::withTrashed()
                ->where('category_id', $category->id)
                ->pluck('accession');

            $maxNumber = 0;
            $maxAccessionString = null;

            foreach ($accessions as $acc) {
                if (preg_match('/(\d+)$/', $acc, $match)) {
                    $num = (int)$match[1];
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                        $maxAccessionString = $acc;
                    }
                }
            }

            if ($maxAccessionString) {
                BkLastAccession::updateOrCreate(
                    ['category_id' => $category->id],
                    ['accession_number' => $maxAccessionString]
                );
                $this->command->info("Category ID {$category->id} updated with last accession: {$maxAccessionString}");
            } else {
                BkLastAccession::where('category_id', $category->id)->delete();
                $this->command->info("Category ID {$category->id} has no valid 6-digit accessions, record deleted/skipped.");
            }
        }

        $this->command->info('BkLastAccession reset successfully completed.');
    }
}
