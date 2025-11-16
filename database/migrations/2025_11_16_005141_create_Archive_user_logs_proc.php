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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `Archive_user_logs`()
BEGIN
    -- Start transaction for consistency
    START TRANSACTION;

    -- Step 1: Archive logs
    INSERT INTO archive_user_logs (
        first_name,
        middle_name,
        last_name,
        computer_use,
        action,
        timestamp,
        archived_at
    )
    SELECT 
        u.first_name,
        u.middle_name,
        u.last_name,
        l.computer_use,
        l.action,
        l.timestamp,
        NOW()
    FROM log_user_logs l
    INNER JOIN usr_users u ON l.user_id = u.id
    WHERE l.timestamp < NOW() - INTERVAL 1 month;

    -- Step 2: Optionally delete logs after archiving
    DELETE FROM log_user_logs
    WHERE timestamp < NOW() - INTERVAL 1 month;

    -- Commit the changes
    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS Archive_user_logs");
    }
};
