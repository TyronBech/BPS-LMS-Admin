<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_bk_books_after_insert` AFTER INSERT ON `bk_books` FOR EACH ROW BEGIN
    DECLARE actor VARCHAR(50);
    SET actor = IFNULL(@current_user_id, 'system');

    INSERT INTO audit_trail
        (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
    VALUES
        (NEW.id, 'bk_books', 'accession', NULL, NEW.accession, 'INSERT', actor),
        (NEW.id, 'bk_books', 'title', NULL, NEW.title, 'INSERT', actor),
        (NEW.id, 'bk_books', 'category_id', NULL, CAST(NEW.category_id AS CHAR), 'INSERT', actor),
        (NEW.id, 'bk_books', 'availability_status', NULL, NEW.availability_status, 'INSERT', actor);
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_bk_books_after_insert`;
SQL);
    }
};
