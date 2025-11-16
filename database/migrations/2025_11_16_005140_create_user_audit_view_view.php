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
        DB::statement("CREATE VIEW `user_audit_view` AS select `db_owlquery`.`aud_user_audit`.`id` AS `id`,`db_owlquery`.`aud_user_audit`.`user_id` AS `user_id`,`db_owlquery`.`aud_user_audit`.`source_table` AS `source_table`,`db_owlquery`.`aud_user_audit`.`field_changed` AS `field_changed`,`db_owlquery`.`aud_user_audit`.`old_value` AS `old_value`,`db_owlquery`.`aud_user_audit`.`new_value` AS `new_value`,`db_owlquery`.`aud_user_audit`.`change_type` AS `change_type`,`db_owlquery`.`aud_user_audit`.`changed_by` AS `changed_by`,`db_owlquery`.`aud_user_audit`.`changed_date` AS `changed_date`,`db_owlquery`.`aud_user_audit`.`created_at` AS `created_at`,`db_owlquery`.`aud_user_audit`.`updated_at` AS `updated_at` from `db_owlquery`.`aud_user_audit`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `user_audit_view`");
    }
};
