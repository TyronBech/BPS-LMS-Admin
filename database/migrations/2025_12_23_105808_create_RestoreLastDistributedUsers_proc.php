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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `RestoreLastDistributedUsers`()
BEGIN
    DECLARE latest_created_at DATETIME;

    -- Get the most recent creation timestamp from usr_users
    SELECT MAX(created_at) INTO latest_created_at FROM usr_users;

    IF latest_created_at IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No recently distributed users found.';
    END IF;

    START TRANSACTION;

    -- Insert only if user does not already exist in usr_staging_users
    INSERT INTO `usr_staging_users` (
        rfid, user_type, first_name, middle_name, last_name, suffix, 
        email, password, profile_image, penalty_total, id_number, level, section,
        employee_id, employee_role, school_org, purpose, gender
    )
    SELECT 
        u.rfid,
        -- Determine user_type from privileges
        CASE 
            WHEN p.user_type = 'student' THEN 'student'
            WHEN p.user_type = 'visitor' THEN 'visitor'
            WHEN p.user_type = 'employee' THEN 'employee'
        END AS user_type,
        u.first_name, u.middle_name, u.last_name, u.suffix,
        u.email, u.password, u.profile_image, u.penalty_total,
        s.id_number, s.level, s.section,
        e.employee_id,
        -- Ensure employee_role is NULL for students & visitors
        CASE 
            WHEN p.user_type = 'employee' THEN p.category 
            ELSE NULL 
        END AS employee_role,  
        v.school_org, v.purpose, v.gender
    FROM `usr_users` u
    LEFT JOIN `privileges` p ON u.privilege_id = p.id  -- Get user type from privileges
    LEFT JOIN `usr_student_details` s ON u.id = s.user_id
    LEFT JOIN `usr_employee_details` e ON u.id = e.user_id
    LEFT JOIN `usr_visitor_details` v ON u.id = v.user_id
    WHERE u.created_at = latest_created_at
    AND NOT EXISTS (
        SELECT 1 FROM `usr_staging_users` su WHERE su.email = u.email
    );  --  Prevents duplicates

    -- Delete only restored users
    DELETE FROM `usr_student_details` WHERE user_id IN (SELECT id FROM `usr_users` WHERE created_at = latest_created_at);
    DELETE FROM `usr_employee_details` WHERE user_id IN (SELECT id FROM `usr_users` WHERE created_at = latest_created_at);
    DELETE FROM `usr_visitor_details` WHERE user_id IN (SELECT id FROM `usr_users` WHERE created_at = latest_created_at);
    DELETE FROM `usr_users` WHERE created_at = latest_created_at;

    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS RestoreLastDistributedUsers");
    }
};
