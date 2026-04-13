<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_tr_transactions_after_update` AFTER UPDATE ON `tr_transactions` FOR EACH ROW BEGIN
    DECLARE actor VARCHAR(50);
    SET actor = IFNULL(@current_user_id, 'system');

    IF NOT (OLD.status <=> NEW.status) THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'status', OLD.status, NEW.status, 'UPDATE', actor);
    END IF;

    IF NOT (OLD.penalty_status <=> NEW.penalty_status) THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'penalty_status', OLD.penalty_status, NEW.penalty_status, 'UPDATE', actor);
    END IF;

    IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
        INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
        VALUES (NEW.id, 'tr_transactions', 'deleted_at', NULL, CAST(NEW.deleted_at AS CHAR), 'DELETE', actor);
    END IF;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_tr_transactions_after_update`;
SQL);
    }
};
