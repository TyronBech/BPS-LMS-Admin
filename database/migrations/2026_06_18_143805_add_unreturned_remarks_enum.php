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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `bk_books` MODIFY COLUMN `remarks` ENUM('On Shelf', 'Unreturned', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced') DEFAULT 'On Shelf'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Update any existing books with remarks 'Unreturned' to 'On Shelf' first before modifying
            DB::table('bk_books')
                ->where('remarks', 'Unreturned')
                ->update(['remarks' => 'On Shelf']);

            DB::statement("ALTER TABLE `bk_books` MODIFY COLUMN `remarks` ENUM('On Shelf', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced') DEFAULT 'On Shelf'");
        }
    }
};
