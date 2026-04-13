<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement("SET @current_user_id = 'seeder'");

        $this->call([
            RolePermissionSeeder::class,
            UserGroupSeeder::class,
            SystemSettingSeeder::class,
            UISettingSeeder::class,
            CategorySeeder::class,
            BookSeeder::class,
            SubjectSeeder::class,
            SubjectAccessCodeSeeder::class,
            UserSeeder::class,
            StudentDetailSeeder::class,
            EmployeeDetailSeeder::class,
            VisitorDetailSeeder::class,
            InventorySeeder::class,
            PenaltyRuleSeeder::class,
            TransactionSeeder::class,
            PenaltySeeder::class,
            ReservationSeeder::class,
            LogSeeder::class,
            NotificationSeeder::class,
            ArchiveInventorySeeder::class,
            AuditTrailSeeder::class,
            StagingUserSeeder::class,
            SuperAdminSeeder::class,
            ResetBookMatrix::class,
        ]);
    }
}
