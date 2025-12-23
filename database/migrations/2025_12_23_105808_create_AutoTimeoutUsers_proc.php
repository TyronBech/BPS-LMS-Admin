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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `AutoTimeoutUsers`()
UPDATE log_user_logs  
    SET 
        time_out = TIMESTAMP(CURRENT_DATE, '15:30:00'),
        remarks = 'System Generated Timeout'  
    WHERE 
        time_out IS NULL  
        AND DATE(time_in) = CURRENT_DATE");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS AutoTimeoutUsers");
    }
};
