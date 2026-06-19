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
        Schema::table('usr_student_details', function (Blueprint $table) {
            $table->string('level', 50)->change();
            $table->string('section', 255)->change();
        });
        Schema::table('privileges', function (Blueprint $table) {
            $table->string('category', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_student_details', function (Blueprint $table) {
            $table->string('level', 15)->change();
            $table->string('section', 100)->change();
        });
        Schema::table('privileges', function (Blueprint $table) {
            $table->string('category', 50)->change();
        });
    }
};
