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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `ArchiveOldInventories`()
BEGIN
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
    
    -- Step 1: Insert data into archive table
    INSERT INTO `archive_inventories` (`book_id`, `accession`, `call_number`, `title`, `author`, `remarks`, `checked_at`)
    SELECT 
        i.`book_id`,
        b.`accession`,
        b.`call_number`,
        b.`title`,
        b.`author`,
        b.`remarks`,
        i.`checked_at`
    FROM 
        `bk_inventories` i
    JOIN 
        `bk_books` b ON i.`book_id` = b.`id`
    WHERE 
        i.`created_at` < NOW() - INTERVAL 1 YEAR;

    -- Step 2: Remove archived data from active tables
    DELETE i
    FROM 
        `bk_inventories` i
    JOIN 
        `bk_books` b ON i.`book_id` = b.`id`
    WHERE 
        i.`created_at` < NOW() - INTERVAL 1 YEAR;

    -- If all operations are successful, commit the transaction
    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS ArchiveOldInventories");
    }
};
