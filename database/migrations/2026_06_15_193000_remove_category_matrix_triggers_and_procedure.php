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
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_books_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_books_after_soft_delete`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `update_summary_matrix`');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. The triggers and stored procedure are retired and replaced by Eloquent events/PHP logic.
    }
};
