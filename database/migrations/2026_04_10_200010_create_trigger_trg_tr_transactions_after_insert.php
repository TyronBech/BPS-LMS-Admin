<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_tr_transactions_after_insert` AFTER INSERT ON `tr_transactions` FOR EACH ROW BEGIN
    DECLARE actor VARCHAR(50);
    SET actor = IFNULL(@current_user_id, 'system');

    IF NEW.user_id IS NOT NULL THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'user_id', NULL, CAST(NEW.user_id AS CHAR), 'INSERT', actor);
    END IF;

    IF NEW.book_id IS NOT NULL THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'book_id', NULL, CAST(NEW.book_id AS CHAR), 'INSERT', actor);
    END IF;

    IF NEW.status IS NOT NULL THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'status', NULL, NEW.status, 'INSERT', actor);
    END IF;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_tr_transactions_after_insert`;
SQL);
    }
};
