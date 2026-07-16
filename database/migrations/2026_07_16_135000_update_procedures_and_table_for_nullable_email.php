<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make email column nullable in usr_staging_users table
        Schema::table('usr_staging_users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Recreate the DistributeStagingUsers stored procedure with the updated join conditions
        DB::unprepared("DROP PROCEDURE IF EXISTS `DistributeStagingUsers`;");
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE `DistributeStagingUsers`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO usr_users (
        rfid, privilege_id, first_name, middle_name, last_name, suffix, gender,
        email, password, profile_image
    )
    SELECT
        su.rfid,
        CASE
            WHEN su.user_type = 'student' THEN (SELECT id FROM privileges WHERE user_type = 'student' LIMIT 1)
            WHEN su.user_type = 'visitor' THEN (SELECT id FROM privileges WHERE user_type = 'visitor' LIMIT 1)
            WHEN su.user_type = 'employee' THEN (SELECT id FROM privileges WHERE user_type = 'employee' AND category = su.employee_role LIMIT 1)
            ELSE NULL
        END,
        su.first_name, su.middle_name, su.last_name, su.suffix, su.gender,
        su.email, su.password, su.profile_image
    FROM usr_staging_users su
    WHERE NOT EXISTS (
        SELECT 1 FROM usr_users u 
        WHERE (su.email IS NOT NULL AND u.email = su.email AND u.deleted_at IS NULL)
           OR (su.email IS NULL AND u.first_name = su.first_name AND u.last_name = su.last_name AND u.gender = su.gender AND u.rfid <=> su.rfid AND u.deleted_at IS NULL)
    );

    INSERT INTO usr_student_details (user_id, id_number, level, section)
    SELECT u.id, su.id_number, su.level, su.section
    FROM usr_staging_users su
    JOIN usr_users u ON 
        (su.email IS NOT NULL AND u.email = su.email)
        OR (su.email IS NULL AND su.rfid IS NOT NULL AND u.rfid = su.rfid)
        OR (su.email IS NULL AND su.rfid IS NULL 
            AND u.first_name = su.first_name 
            AND u.last_name = su.last_name 
            AND u.gender = su.gender 
            AND u.middle_name <=> su.middle_name 
            AND u.suffix <=> su.suffix)
    WHERE su.user_type = 'student' AND su.id_number IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_student_details sd WHERE sd.user_id = u.id);

    INSERT INTO usr_employee_details (user_id, employee_id, employee_role)
    SELECT u.id, su.employee_id, su.employee_role
    FROM usr_staging_users su
    JOIN usr_users u ON 
        (su.email IS NOT NULL AND u.email = su.email)
        OR (su.email IS NULL AND su.rfid IS NOT NULL AND u.rfid = su.rfid)
        OR (su.email IS NULL AND su.rfid IS NULL 
            AND u.first_name = su.first_name 
            AND u.last_name = su.last_name 
            AND u.gender = su.gender 
            AND u.middle_name <=> su.middle_name 
            AND u.suffix <=> su.suffix)
    WHERE su.user_type = 'employee' AND su.employee_id IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_employee_details ed WHERE ed.user_id = u.id);

    INSERT INTO usr_visitor_details (user_id, school_org, purpose)
    SELECT u.id, su.school_org, su.purpose
    FROM usr_staging_users su
    JOIN usr_users u ON 
        (su.email IS NOT NULL AND u.email = su.email)
        OR (su.email IS NULL AND su.rfid IS NOT NULL AND u.rfid = su.rfid)
        OR (su.email IS NULL AND su.rfid IS NULL 
            AND u.first_name = su.first_name 
            AND u.last_name = su.last_name 
            AND u.gender = su.gender 
            AND u.middle_name <=> su.middle_name 
            AND u.suffix <=> su.suffix)
    WHERE su.user_type = 'visitor' AND su.school_org IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_visitor_details vd WHERE vd.user_id = u.id);

    DELETE FROM usr_staging_users;

    COMMIT;
END;
SQL
        );

        // Recreate the RestoreLastDistributedUsers stored procedure with the updated join conditions
        DB::unprepared("DROP PROCEDURE IF EXISTS `RestoreLastDistributedUsers`;");
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE `RestoreLastDistributedUsers`()
BEGIN
    DECLARE latest_created_at DATETIME;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT MAX(created_at) INTO latest_created_at FROM usr_users;

    IF latest_created_at IS NOT NULL THEN
        INSERT INTO usr_staging_users (
            rfid, first_name, middle_name, last_name, suffix, gender, email, password,
            profile_image, user_type, id_number, level, section, employee_id, employee_role,
            school_org, purpose, created_at, updated_at
        )
        SELECT
            u.rfid,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.suffix,
            u.gender,
            u.email,
            u.password,
            u.profile_image,
            COALESCE(p.user_type, 'visitor'),
            sd.id_number,
            sd.level,
            sd.section,
            ed.employee_id,
            ed.employee_role,
            vd.school_org,
            vd.purpose,
            NOW(),
            NOW()
        FROM usr_users u
        LEFT JOIN privileges p ON p.id = u.privilege_id
        LEFT JOIN usr_student_details sd ON sd.user_id = u.id
        LEFT JOIN usr_employee_details ed ON ed.user_id = u.id
        LEFT JOIN usr_visitor_details vd ON vd.user_id = u.id
        WHERE u.created_at = latest_created_at
          AND NOT EXISTS (
              SELECT 1 FROM usr_staging_users su 
              WHERE (su.email IS NOT NULL AND su.email = u.email)
                 OR (su.email IS NULL AND u.email IS NULL 
                     AND su.first_name = u.first_name 
                     AND su.last_name = u.last_name 
                     AND su.gender = u.gender 
                     AND su.rfid <=> u.rfid)
          );

        DELETE FROM usr_student_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_employee_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_visitor_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_users WHERE created_at = latest_created_at;
    END IF;

    COMMIT;
END;
SQL
        );
    }

    public function down(): void
    {
        // Revert RestoreLastDistributedUsers
        DB::unprepared("DROP PROCEDURE IF EXISTS `RestoreLastDistributedUsers`;");
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE `RestoreLastDistributedUsers`()
BEGIN
    DECLARE latest_created_at DATETIME;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT MAX(created_at) INTO latest_created_at FROM usr_users;

    IF latest_created_at IS NOT NULL THEN
        INSERT INTO usr_staging_users (
            rfid, first_name, middle_name, last_name, suffix, gender, email, password,
            profile_image, user_type, id_number, level, section, employee_id, employee_role,
            school_org, purpose, created_at, updated_at
        )
        SELECT
            u.rfid,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.suffix,
            u.gender,
            u.email,
            u.password,
            u.profile_image,
            COALESCE(p.user_type, 'visitor'),
            sd.id_number,
            sd.level,
            sd.section,
            ed.employee_id,
            ed.employee_role,
            vd.school_org,
            vd.purpose,
            NOW(),
            NOW()
        FROM usr_users u
        LEFT JOIN privileges p ON p.id = u.privilege_id
        LEFT JOIN usr_student_details sd ON sd.user_id = u.id
        LEFT JOIN usr_employee_details ed ON ed.user_id = u.id
        LEFT JOIN usr_visitor_details vd ON vd.user_id = u.id
        WHERE u.created_at = latest_created_at
          AND NOT EXISTS (SELECT 1 FROM usr_staging_users su WHERE su.email = u.email);

        DELETE FROM usr_student_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_employee_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_visitor_details WHERE user_id IN (SELECT id FROM usr_users WHERE created_at = latest_created_at);
        DELETE FROM usr_users WHERE created_at = latest_created_at;
    END IF;

    COMMIT;
END;
SQL
        );

        // Revert DistributeStagingUsers
        DB::unprepared("DROP PROCEDURE IF EXISTS `DistributeStagingUsers`;");
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE `DistributeStagingUsers`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO usr_users (
        rfid, privilege_id, first_name, middle_name, last_name, suffix, gender,
        email, password, profile_image
    )
    SELECT
        su.rfid,
        CASE
            WHEN su.user_type = 'student' THEN (SELECT id FROM privileges WHERE user_type = 'student' LIMIT 1)
            WHEN su.user_type = 'visitor' THEN (SELECT id FROM privileges WHERE user_type = 'visitor' LIMIT 1)
            WHEN su.user_type = 'employee' THEN (SELECT id FROM privileges WHERE user_type = 'employee' AND category = su.employee_role LIMIT 1)
            ELSE NULL
        END,
        su.first_name, su.middle_name, su.last_name, su.suffix, su.gender,
        su.email, su.password, su.profile_image
    FROM usr_staging_users su
    WHERE NOT EXISTS (
        SELECT 1 FROM usr_users u WHERE u.email = su.email AND u.deleted_at IS NULL
    );

    INSERT INTO usr_student_details (user_id, id_number, level, section)
    SELECT u.id, su.id_number, su.level, su.section
    FROM usr_staging_users su
    JOIN usr_users u ON u.email = su.email
    WHERE su.user_type = 'student' AND su.id_number IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_student_details sd WHERE sd.user_id = u.id);

    INSERT INTO usr_employee_details (user_id, employee_id, employee_role)
    SELECT u.id, su.employee_id, su.employee_role
    FROM usr_staging_users su
    JOIN usr_users u ON u.email = su.email
    WHERE su.user_type = 'employee' AND su.employee_id IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_employee_details ed WHERE ed.user_id = u.id);

    INSERT INTO usr_visitor_details (user_id, school_org, purpose)
    SELECT u.id, su.school_org, su.purpose
    FROM usr_staging_users su
    JOIN usr_users u ON u.email = su.email
    WHERE su.user_type = 'visitor' AND su.school_org IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM usr_visitor_details vd WHERE vd.user_id = u.id);

    DELETE FROM usr_staging_users;

    COMMIT;
END;
SQL
        );

        // Revert email nullability
        Schema::table('usr_staging_users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
