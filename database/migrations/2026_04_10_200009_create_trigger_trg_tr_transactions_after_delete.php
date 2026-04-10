<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_tr_transactions_after_delete` AFTER DELETE ON `tr_transactions` FOR EACH ROW BEGIN
    DECLARE actor VARCHAR(50);
    SET actor = IFNULL(@current_user_id, 'system');

    INSERT INTO audit_trail (record_id, source_table, field_changed, old_value, new_value, action_type, changed_by)
    VALUES
        (OLD.id, 'tr_transactions', 'user_id', CAST(OLD.user_id AS CHAR), NULL, 'DELETE', actor),
        (OLD.id, 'tr_transactions', 'book_id', CAST(OLD.book_id AS CHAR), NULL, 'DELETE', actor),
        (OLD.id, 'tr_transactions', 'status', OLD.status, NULL, 'DELETE', actor),
        (OLD.id, 'tr_transactions', 'penalty_status', OLD.penalty_status, NULL, 'DELETE', actor);
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_tr_transactions_after_delete`;
SQL);
    }
};
