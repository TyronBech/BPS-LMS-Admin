<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;

class BookMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Print', 'Non-print', 'E-books'];
        $availabilityStatuses = ['Available', 'Unavailable', 'Borrowed', 'In Use', 'Reserved'];
        $conditionStatuses = ['New', 'Good', 'Fair', 'Poor'];
        $remarksList = ['On Shelf', 'Unreturned', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced'];

        $barcodeGenerator = new DNS1D();

        foreach ($types as $type) {
            // Create 5 categories for this type
            for ($i = 1; $i <= 5; $i++) {
                $category = Category::create([
                    'legend' => strtoupper(Str::random(3)),
                    'name' => "{$type} Category {$i}",
                    'category_type' => $type,
                    'borrow_duration_days' => ($type === 'Print') ? 7 : 0,
                    'present_inventory' => 0,
                ]);

                // Create 40 books for this book_type (spread across the 5 categories of this type)
                // We divide 40 by 5 to give 8 books per category, but let's just loop 40 times 
                // and pick a random category of this type later to keep it simpler if you want exactly 40 per type.
            }

            // Get all categories for this specific type to assign books correctly
            $categoriesOfThisType = Category::where('category_type', $type)->get();

            for ($j = 1; $j <= 40; $j++) {
                $accession = strtoupper($type[0]) . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT) . $j;
                $category = $categoriesOfThisType->random();

                // Randomize availability and remarks with some logic
                $remarks = $remarksList[array_rand($remarksList)];
                $availability = ($remarks === 'On Shelf') ? 'Available' : 'Unavailable';
                
                // Override with some random "Borrowed" or "Reserved" for variety if it was available
                if ($availability === 'Available' && rand(1, 10) > 7) {
                    $availability = $availabilityStatuses[array_rand(['Borrowed', 'In Use', 'Reserved'])];
                }

                Book::create([
                    'accession' => $accession,
                    'call_number' => "CN-" . rand(100, 999) . "." . rand(10, 99),
                    'title' => "Sample {$type} Book " . Str::random(5),
                    'author' => "Author " . rand(1, 50),
                    'description' => "This is a sample description for a {$type} book.",
                    'edition' => rand(1, 5) . "th Edition",
                    'isbn' => rand(100, 999) . "-" . rand(10, 99) . "-" . rand(10000, 99999),
                    'place_of_publication' => "City " . rand(1, 10),
                    'publisher' => "Publisher " . rand(1, 20),
                    'copyrights' => rand(2000, 2024),
                    'remarks' => $remarks,
                    'category_id' => $category->id,
                    'book_type' => $type,
                    'availability_status' => $availability,
                    'condition_status' => $conditionStatuses[array_rand($conditionStatuses)],
                    'barcode' => $barcodeGenerator->getBarcodeJPG($accession, 'C39', 2, 80, array(0, 0, 0, 0), false),
                ]);
            }
        }
    }
}
