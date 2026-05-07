<?php

namespace Database\Seeders;

use App\Enum\PermissionsEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * SyncPermissionsSeeder — Dynamic permission sync.
 *
 * This seeder reads every case in PermissionsEnum and ensures it exists
 * in the `permissions` table with guard_name = 'admin'.
 * It uses firstOrCreate, so it is safe to run on every deployment —
 * it will never duplicate existing permissions and will only INSERT
 * newly added ones.
 *
 * Additionally, the Super Admin role is always kept in sync with all
 * permissions so it never misses a newly introduced one.
 *
 * Usage:
 *   php artisan db:seed --class=SyncPermissionsSeeder
 *
 * In GitHub Actions (deploy YAML), add this after optimize:clear:
 *   php artisan db:seed --class=SyncPermissionsSeeder --force
 */
class SyncPermissionsSeeder extends Seeder
{
    /**
     * Sync all PermissionsEnum cases to the permissions table.
     *
     * @return void
     */
    public function run(): void
    {
        // Flush cached permissions so Spatie picks up changes immediately.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $createdCount  = 0;
        $existingCount = 0;

        foreach (PermissionsEnum::cases() as $permissionCase) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionCase->value, 'guard_name' => 'admin']
            );

            if ($permission->wasRecentlyCreated) {
                $createdCount++;
                Log::info('SyncPermissionsSeeder: New permission seeded', [
                    'name'       => $permissionCase->value,
                    'enum_case'  => $permissionCase->name,
                    'timestamp'  => now(),
                ]);
                $this->command->line("  <fg=green>CREATED</> {$permissionCase->value}");
            } else {
                $existingCount++;
                $this->command->line("  <fg=gray>EXISTS </> {$permissionCase->value}");
            }
        }

        // Keep the Super Admin role in sync with every permission so it
        // always has access to newly added capabilities automatically.
        $superAdminRole = Role::where('name', 'Super Admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($superAdminRole !== null) {
            $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());
            $this->command->info('Super Admin role synced with all permissions.');
        }

        // Flush again after changes are committed.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->newLine();
        $this->command->info("Permissions sync complete: {$createdCount} created, {$existingCount} already existed.");
    }
}
