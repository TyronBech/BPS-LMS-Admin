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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `update_summary_matrix`()
BEGIN
   -- Start transaction for consistency
    START TRANSACTION;

    -- Step 1: Archive previous data
    INSERT INTO archive_categories (
        legend,
        name,
        previous_inventory,
        newly_acquired,
        discarded,
        present_inventory,
        archived_at
    )
    SELECT 
        legend,
        name,
        previous_inventory,
        newly_acquired,
        discarded,
        present_inventory,
        NOW()
    FROM bk_categories;

    -- Step 2: Update current inventory
    UPDATE bk_categories
    SET 
        previous_inventory = present_inventory,
        newly_acquired = 0,
        discarded = 0;

    -- Commit the changes
    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS update_summary_matrix");
    }
};
