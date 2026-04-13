<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_books_after_insert` AFTER INSERT ON `bk_books` FOR EACH ROW BEGIN
    UPDATE bk_categories
    SET newly_acquired = newly_acquired + 1,
        present_inventory = present_inventory + 1
    WHERE id = NEW.category_id;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_books_after_insert`;
SQL);
    }
};
