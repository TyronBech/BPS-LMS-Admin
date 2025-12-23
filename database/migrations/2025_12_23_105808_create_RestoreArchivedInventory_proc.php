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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `RestoreArchivedInventory`(IN `archive_id` BIGINT)
BEGIN
    DECLARE book_exists INT;
    
    -- Declare an error handler to handle transaction failures
    DECLARE EXIT HANDLER FOR SQLEXCEPTION  
    BEGIN
        -- Rollback transaction if any error occurs
        ROLLBACK;
        -- Print an error message
        SELECT 'An error occurred. Transaction rolled back.' AS message;
    END;

    -- Start the transaction
    START TRANSACTION;
    
    -- Check if the book ID exists in `bk_books`
    SELECT COUNT(*) INTO book_exists
    FROM `bk_books`
    WHERE `id` = (SELECT `book_id` FROM `archive_inventories` WHERE `id` = archive_id);

    IF book_exists > 0 THEN
        -- Restore data to `bk_inventories`
        INSERT INTO `bk_inventories` (`book_id`, `checked_at`, `created_at`, `updated_at`)
        SELECT 
            `book_id`, 
            `checked_at`,
            NOW(),
            NOW()
        FROM 
            `archive_inventories`
        WHERE 
            `id` = archive_id;
        
        -- Remove from archive after restoring
        DELETE FROM `archive_inventories`
        WHERE `id` = archive_id;
        
        -- If everything is successful, commit the transaction
        COMMIT;
    ELSE
        -- If book does not exist, rollback the transaction
        ROLLBACK;
        SELECT 'Book ID does not exist. Restoration canceled.' AS message;
    END IF;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS RestoreArchivedInventory");
    }
};
