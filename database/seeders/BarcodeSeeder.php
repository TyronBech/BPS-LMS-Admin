<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Milon\Barcode\DNS1D;

class BarcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Book::all()->each(function ($book) {
            $barcode = (new DNS1D())->getBarcodeJPG($book->accession, 'C39', 2, 80, array(0, 0, 0, 0), false);
            $book->update([
                'barcode' => $barcode
            ]);
        });
    }
}
