<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_books_after_soft_delete` AFTER UPDATE ON `bk_books` FOR EACH ROW BEGIN
    IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
        -- Book was soft-deleted
        UPDATE bk_categories
        SET discarded = discarded + 1,
            present_inventory = present_inventory - 1
        WHERE id = NEW.category_id;
    ELSEIF OLD.deleted_at IS NOT NULL AND NEW.deleted_at IS NULL THEN
        -- Book was restored
        UPDATE bk_categories
        SET discarded = discarded - 1,
            present_inventory = present_inventory + 1
        WHERE id = NEW.category_id;
    ELSEIF OLD.deleted_at IS NULL AND NEW.deleted_at IS NULL AND NOT (OLD.category_id <=> NEW.category_id) THEN
        -- Book's category was changed while active; adjust present_inventory only.
        -- newly_acquired reflects books added in the current year, not category membership.
        UPDATE bk_categories
        SET present_inventory = present_inventory - 1
        WHERE id = OLD.category_id;

        UPDATE bk_categories
        SET present_inventory = present_inventory + 1
        WHERE id = NEW.category_id;
    END IF;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `trg_books_after_soft_delete`;
SQL);
    }
};
