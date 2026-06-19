<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_staging_users', function (Blueprint $table) {
            $table->string('employee_role', 255)->nullable()->change();
            $table->string('section', 255)->nullable()->change();
        });
        Schema::table('usr_employee_details', function (Blueprint $table) {
            $table->string('employee_role', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
