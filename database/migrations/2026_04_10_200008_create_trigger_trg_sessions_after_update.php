<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_sessions_after_update` AFTER UPDATE ON `sessions` FOR EACH ROW INSERT INTO audit_trail (
    record_id, source_table, field_changed, old_value, new_value, action_type, changed_by
)
SELECT
    NEW.user_id,
    'sessions',
    'login_source',
    NULL,
    NEW.login_source,
    'LOGIN',
    NEW.user_id
WHERE OLD.user_id IS NULL AND NEW.user_id IS NOT NULL
UNION ALL
SELECT
    OLD.user_id,
    'sessions',
    'login_source',
    OLD.login_source,
    NULL,
    'LOGOUT',
    OLD.user_id
WHERE OLD.user_id IS NOT NULL AND NEW.user_id IS NULL;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_sessions_after_update`;
SQL);
    }
};
