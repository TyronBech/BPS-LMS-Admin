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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `archive_transactions`()
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    -- Rollback the transaction
    ROLLBACK;

    -- Show rollback message
    SIGNAL SQLSTATE '45000' 
      SET MESSAGE_TEXT = 'Transaction rolled back due to an error during archiving.';
  END;

  START TRANSACTION;

  -- Insert into archive table
  INSERT INTO archive_transactions (
    transaction_id, transaction_type, status, date_borrowed, due_date, return_date,
    book_condition, penalty_total, penalty_status, transaction_remarks,

    user_id, rfid, full_name, gender, user_type, category,

    book_id, book_category_name, accession, call_number, title, author, edition,
    availability_status, condition_status,

    user_time_in, user_time_out,
    notif_title, notif_message,

    archived_at
  )
  SELECT
    t.id, t.transaction_type, 
    CASE 
      WHEN t.status IN ('Borrowed', 'Completed', 'Overdue', 'Lost', 'Pending', 'Cancelled', 'Missing') THEN t.status 
      ELSE 'Pending' 
    END AS status,
    t.date_borrowed, t.due_date, t.return_date,
    t.book_condition, t.penalty_total, t.penalty_status, t.remarks,

    u.id, u.rfid,
    CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name, ' ', IFNULL(u.suffix, '')) AS full_name,
    u.gender, p.user_type, p.category,

    b.id, c.name AS book_category_name, b.accession, b.call_number, b.title, b.author, b.edition,
    b.availability_status, b.condition_status,

    (
      SELECT l.timestamp
      FROM log_user_logs l
      WHERE l.user_id = u.id AND l.action = 'Time in' AND DATE(l.timestamp) <= DATE(t.return_date)
      ORDER BY l.timestamp DESC
      LIMIT 1
    ) AS user_time_in,

    (
      SELECT l.timestamp
      FROM log_user_logs l
      WHERE l.user_id = u.id AND l.action = 'Time out' AND DATE(l.timestamp) <= DATE(t.return_date)
      ORDER BY l.timestamp DESC
      LIMIT 1
    ) AS user_time_out,

    (
      SELECT n.title
      FROM notifications n
      WHERE n.user_id = u.id AND n.transaction_id = t.id
      ORDER BY n.notif_date DESC
      LIMIT 1
    ) AS notif_title,

    (
      SELECT n.message
      FROM notifications n
      WHERE n.user_id = u.id AND n.transaction_id = t.id
      ORDER BY n.notif_date DESC
      LIMIT 1
    ) AS notif_message,

    NOW()
  FROM tr_transactions t
  JOIN usr_users u ON t.user_id = u.id
  LEFT JOIN privileges p ON u.privilege_id = p.id
  JOIN bk_books b ON t.book_id = b.id
  LEFT JOIN bk_categories c ON b.category_id = c.id
  WHERE t.status IN ('Completed', 'Overdue', 'Lost', 'Missing')
    AND (t.penalty_status IS NULL OR t.penalty_status IN ('No Penlaty', 'Paid', 'Waived'));

  -- Delete the archived rows from original table
  DELETE FROM tr_transactions
  WHERE status IN ('Completed', 'Overdue', 'Lost','Missing')
    AND (penalty_status IS NULL OR penalty_status IN ('No Penalty','Paid', 'Waived'));

  COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS archive_transactions");
    }
};
