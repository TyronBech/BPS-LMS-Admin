<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usr_staging_users', function (Blueprint $table) {
            $table->id();
            $table->string('rfid', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 10)->nullable();
            $table->enum('gender', ['Male','Female','Prefer not to say']);
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('user_type', ['student','employee','visitor']);
            $table->string('id_number', 20)->nullable();
            $table->string('level', 50)->nullable();
            $table->string('section', 100)->nullable();
            $table->string('employee_id', 50)->nullable();
            $table->string('employee_role', 45)->nullable();
            $table->string('school_org', 100)->nullable();
            $table->string('purpose', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usr_staging_users');
    }
};