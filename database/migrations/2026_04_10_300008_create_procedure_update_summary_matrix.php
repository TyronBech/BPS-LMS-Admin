<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
DROP PROCEDURE IF EXISTS `update_summary_matrix`;
CREATE PROCEDURE `update_summary_matrix`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO archive_categories (
        category_id, legend, name, previous_inventory, newly_acquired, discarded, present_inventory, archived_at
    )
    SELECT
        id, legend, name, previous_inventory, newly_acquired, discarded, present_inventory, NOW()
    FROM bk_categories;

    UPDATE bk_categories
    SET previous_inventory = present_inventory,
        newly_acquired = 0,
        discarded = 0;

    COMMIT;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP PROCEDURE IF EXISTS `update_summary_matrix`;
SQL);
    }
};
