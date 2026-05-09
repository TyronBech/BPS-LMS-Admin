<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. EXPAND: Allow both old and new values so MySQL doesn't throw a strict mode error
        DB::statement("ALTER TABLE bk_books MODIFY COLUMN book_type ENUM('physical', 'ebook', 'Print', 'Non-print', 'E-books') DEFAULT 'Print'");

        // 2. UPDATE: Change the data using the DB facade (Not the Book model)
        DB::table('bk_books')
            ->where('book_type', 'physical')
            ->update(['book_type' => 'Print']);

        // (Optional: You may also want to map 'ebook' to 'E-books' here)
        DB::table('bk_books')
            ->where('book_type', 'ebook')
            ->update(['book_type' => 'E-books']);

        // 3. RESTRICT: Lock the column down to ONLY the new values
        DB::statement("ALTER TABLE bk_books MODIFY COLUMN book_type ENUM('Print', 'Non-print', 'E-books') DEFAULT 'Print'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. EXPAND: Allow both old and new values
        DB::statement("ALTER TABLE bk_books MODIFY COLUMN book_type ENUM('physical', 'ebook', 'Print', 'Non-print', 'E-books') DEFAULT 'physical'");

        // 2. UPDATE: Revert the data
        DB::table('bk_books')
            ->where('book_type', 'Print')
            ->update(['book_type' => 'physical']);

        DB::table('bk_books')
            ->where('book_type', 'E-books')
            ->update(['book_type' => 'ebook']);

        // 3. RESTRICT: Lock the column down to ONLY the old values
        DB::statement("ALTER TABLE bk_books MODIFY COLUMN book_type ENUM('physical', 'ebook') DEFAULT 'physical'");
    }
};
