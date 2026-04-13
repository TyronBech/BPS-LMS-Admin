<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
DROP PROCEDURE IF EXISTS `archive_transactions`;
CREATE PROCEDURE `archive_transactions`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO archive_transactions (
        transaction_id, transaction_type, status, date_borrowed, due_date, return_date,
        book_condition, penalty_total, penalty_status, transaction_remarks,
        user_id, rfid, full_name, gender, user_type, category,
        book_id, book_category_name, accession, call_number, title, author, edition,
        availability_status, condition_status,
        archived_at
    )
    SELECT
        t.id, t.transaction_type, t.status, t.date_borrowed, t.due_date, t.return_date,
        t.book_condition, t.penalty_total, t.penalty_status, t.remarks,
        u.id, u.rfid,
        CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name, ' ', IFNULL(u.suffix, '')),
        u.gender, p.user_type, p.category,
        b.id, c.name, b.accession, b.call_number, b.title, b.author, b.edition,
        b.availability_status, b.condition_status,
        NOW()
    FROM tr_transactions t
    JOIN usr_users u ON u.id = t.user_id
    LEFT JOIN privileges p ON p.id = u.privilege_id
    JOIN bk_books b ON b.id = t.book_id
    LEFT JOIN bk_categories c ON c.id = b.category_id
    WHERE t.status IN ('Completed', 'Overdue', 'Lost', 'Missing')
      AND (t.penalty_status IS NULL OR t.penalty_status IN ('No Penalty', 'Paid', 'Waived'));

    DELETE FROM tr_transactions
    WHERE status IN ('Completed', 'Overdue', 'Lost', 'Missing')
      AND (penalty_status IS NULL OR penalty_status IN ('No Penalty', 'Paid', 'Waived'));

    COMMIT;
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP PROCEDURE IF EXISTS `archive_transactions`;
SQL);
    }
};
