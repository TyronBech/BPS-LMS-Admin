<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
DROP EVENT IF EXISTS `YearlyArchiveInventories`;
CREATE EVENT `YearlyArchiveInventories` ON SCHEDULE EVERY 1 YEAR STARTS '2025-03-21 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL ArchiveOldInventories();
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP EVENT IF EXISTS `YearlyArchiveInventories`;
SQL);
    }
};
