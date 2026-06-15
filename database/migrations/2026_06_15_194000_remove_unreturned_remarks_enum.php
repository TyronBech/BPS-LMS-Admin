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
        // 1. Update any existing books with remarks 'Unreturned' to 'On Shelf'
        DB::table('bk_books')
            ->where('remarks', 'Unreturned')
            ->update(['remarks' => 'On Shelf']);

        // 2. Modify the enum column to remove 'Unreturned'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `bk_books` MODIFY COLUMN `remarks` ENUM('On Shelf', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced') DEFAULT 'On Shelf'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `bk_books` MODIFY COLUMN `remarks` ENUM('On Shelf', 'Unreturned', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced') DEFAULT 'On Shelf'");
        }
    }
};
