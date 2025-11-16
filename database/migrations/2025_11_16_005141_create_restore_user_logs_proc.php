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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `restore_user_logs`(IN `restore_date` DATE)
BEGIN
    START TRANSACTION;

    -- Restore data from archive (based on a date range)
    INSERT INTO log_user_logs (
        user_id,
        computer_use,
        action,
        timestamp,
        created_at,
        updated_at
    )
    SELECT 
        u.id, 
        a.computer_use,
        a.action,
        a.timestamp,
        NOW(), 
        NOW()
    FROM archive_user_logs a
    INNER JOIN usr_users u ON 
        a.first_name = u.first_name 
        AND (a.middle_name = u.middle_name OR a.middle_name IS NULL) 
        AND a.last_name = u.last_name
    WHERE DATE(a.timestamp) = restore_date;

    -- Optionally delete from archive after restore
     DELETE FROM archive_user_logs WHERE DATE(timestamp) = restore_date;

    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS restore_user_logs");
    }
};
