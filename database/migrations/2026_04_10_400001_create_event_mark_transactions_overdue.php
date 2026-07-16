<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
DROP EVENT IF EXISTS `mark_transactions_overdue`;
CREATE EVENT `mark_transactions_overdue` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-05-11 09:51:36' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE tr_transactions SET status = 'Overdue', penalty_status = 'Unpaid' WHERE due_date < CURDATE() AND return_date IS NULL AND status = 'Borrowed';
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP EVENT IF EXISTS `mark_transactions_overdue`;
SQL);
    }
};
