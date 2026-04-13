<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_bk_books_after_update` AFTER UPDATE ON `bk_books` FOR EACH ROW BEGIN
    DECLARE actor VARCHAR(50);
    SET actor = IFNULL(@current_user_id, 'system');

    IF NOT (OLD.title <=> NEW.title) THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'bk_books', 'title', OLD.title, NEW.title, 'UPDATE', actor);
    END IF;

    IF NOT (OLD.category_id <=> NEW.category_id) THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'bk_books', 'category_id', CAST(OLD.category_id AS CHAR), CAST(NEW.category_id AS CHAR), 'UPDATE', actor);
    END IF;

    IF NOT (OLD.availability_status <=> NEW.availability_status) THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'bk_books', 'availability_status', OLD.availability_status, NEW.availability_status, 'UPDATE', actor);
    END IF;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_bk_books_after_update`;
SQL);
    }
};
