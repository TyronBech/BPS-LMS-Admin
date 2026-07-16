<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_sessions_after_insert` AFTER INSERT ON `sessions` FOR EACH ROW INSERT INTO audit_trail (
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
WHERE NEW.user_id IS NOT NULL AND NEW.login_source IS NOT NULL;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_sessions_after_insert`;
SQL);
    }
};
