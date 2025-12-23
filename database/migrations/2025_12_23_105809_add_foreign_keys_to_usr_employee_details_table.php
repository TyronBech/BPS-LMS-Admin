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
        Schema::table('usr_employee_details', function (Blueprint $table) {
            $table->foreign(['user_id'], 'usr_employee_details_ibfk_1')->references(['id'])->on('usr_users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_employee_details', function (Blueprint $table) {
            $table->dropForeign('usr_employee_details_ibfk_1');
        });
    }
};
