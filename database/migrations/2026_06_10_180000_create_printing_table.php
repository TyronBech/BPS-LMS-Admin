<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('printing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('usr_student_details')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('usr_employee_details')->onDelete('cascade');
            $table->string('type'); // 'print' or 'photocopy'
            $table->string('title_of_material')->nullable(); // only for photocopy
            $table->string('topic');
            $table->integer('pages');
            $table->decimal('amount', 8, 2)->nullable(); // only for photocopy
            $table->timestamp('printed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert new permissions
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $p1 = Permission::firstOrCreate(['name' => 'Create Printing Entry', 'guard_name' => 'admin']);
            $p2 = Permission::firstOrCreate(['name' => 'View Printing Reports', 'guard_name' => 'admin']);

            $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo([$p1, $p2]);
            }

            $admin = Role::where('name', 'Admin')->where('guard_name', 'admin')->first();
            if ($admin) {
                $admin->givePermissionTo([$p1, $p2]);
            }

            $librarian = Role::where('name', 'Librarian')->where('guard_name', 'admin')->first();
            if ($librarian) {
                $librarian->givePermissionTo([$p1, $p2]);
            }
            
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Log and allow migration to proceed if roles are not seeded yet
            logger('Failed to seed permissions in printing migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printing');

        try {
            Permission::whereIn('name', ['Create Printing Entry', 'View Printing Reports'])->where('guard_name', 'admin')->delete();
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            logger('Failed to delete permissions in printing rollback: ' . $e->getMessage());
        }
    }
};
