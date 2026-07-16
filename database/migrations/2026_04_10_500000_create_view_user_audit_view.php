<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `user_audit_view`;
CREATE VIEW `user_audit_view` AS select `audit_trail`.`id` AS `id`,`audit_trail`.`record_id` AS `user_id`,`audit_trail`.`source_table` AS `source_table`,`audit_trail`.`field_changed` AS `field_changed`,`audit_trail`.`old_value` AS `old_value`,`audit_trail`.`new_value` AS `new_value`,`audit_trail`.`action_type` AS `change_type`,`audit_trail`.`changed_by` AS `changed_by`,`audit_trail`.`created_at` AS `created_at`,`audit_trail`.`updated_at` AS `updated_at` from `audit_trail`;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `user_audit_view`;
SQL);
    }
};
