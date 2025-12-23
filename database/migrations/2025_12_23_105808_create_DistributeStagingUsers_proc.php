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
        DB::unprepared("CREATE DEFINER=`u815439804_stagelms`@`127.0.0.1` PROCEDURE `DistributeStagingUsers`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transaction failed. Rolled back.';
    END;

    START TRANSACTION;
    
    -- Insert common data into usr_users
    INSERT INTO `usr_users` (
        rfid, privilege_id, first_name, middle_name, last_name, suffix, gender, 
        email, password, profile_image
    )
    SELECT 
        su.rfid,
        CASE 
            WHEN su.user_type = 'student' THEN (SELECT id FROM `privileges` WHERE user_type = 'student' LIMIT 1)
            WHEN su.user_type = 'visitor' THEN (SELECT id FROM `privileges` WHERE user_type = 'visitor' LIMIT 1)
            WHEN su.user_type = 'employee' THEN 
                (SELECT id FROM `privileges` 
                 WHERE user_type = 'employee' AND category = su.employee_role 
                 LIMIT 1)
            ELSE NULL
        END,
        su.first_name, su.middle_name, su.last_name, su.suffix, su.gender, -- Moved gender here
        su.email, su.password, su.profile_image
    FROM `usr_staging_users` su
    WHERE NOT EXISTS (
        SELECT 1 FROM `usr_users` u WHERE u.email = su.email AND u.deleted_at IS NULL
    );

    -- Insert student details
    INSERT INTO `usr_student_details` (
        user_id, id_number, level, section
    )
    SELECT 
        u.id,
        su.id_number,
        su.level,
        su.section
    FROM `usr_staging_users` su
    JOIN `usr_users` u ON su.email = u.email
    WHERE su.user_type = 'student'
    AND su.id_number IS NOT NULL
 AND NOT EXISTS (
    SELECT 1 FROM usr_student_details sd WHERE sd.user_id = u.id
);


    -- Insert employee details
    INSERT INTO `usr_employee_details` (
        user_id, employee_id, employee_role
    )
    SELECT 
        u.id,
        su.employee_id,
        su.employee_role
    FROM `usr_staging_users` su
    JOIN `usr_users` u ON su.email = u.email
    WHERE su.user_type = 'employee'
    AND su.employee_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM usr_employee_details ed WHERE ed.user_id = u.id
);

    -- Insert visitor details (Removed gender column)
    INSERT INTO `usr_visitor_details` (
        user_id, school_org, purpose
    )
    SELECT 
        u.id,
        su.school_org,
        su.purpose
    FROM `usr_staging_users` su
    JOIN `usr_users` u ON su.email = u.email
    WHERE su.user_type = 'visitor'
    AND su.school_org IS NOT NULL;

   -- 3. Validation: Check that each user inserted (matched via email from staging_users)
    -- has a corresponding detail record in the proper details table.
    SET @missingDetails = (
      SELECT COUNT(*)
      FROM usr_staging_users su
      JOIN usr_users u ON u.email = su.email
      WHERE NOT (
             (su.user_type = 'student' AND EXISTS (SELECT 1 FROM usr_student_details sd WHERE sd.user_id = u.id))
          OR (su.user_type = 'visitor' AND EXISTS (SELECT 1 FROM usr_visitor_details vd WHERE vd.user_id = u.id))
          OR (su.user_type = 'employee' AND EXISTS (SELECT 1 FROM usr_employee_details ed WHERE ed.user_id = u.id))
      )
    );
    
    IF @missingDetails > 0 THEN
       ROLLBACK;
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Some users do not have corresponding details records. Transaction rolled back.';
    END IF;
    
    -- 4. Only if all users have the appropriate details, proceed to delete from staging_users.
    DELETE FROM usr_staging_users;
    
    COMMIT;
END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS DistributeStagingUsers");
    }
};
