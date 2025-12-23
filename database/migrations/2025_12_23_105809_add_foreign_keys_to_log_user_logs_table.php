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
        Schema::table('log_user_logs', function (Blueprint $table) {
            $table->foreign(['user_id'], 'log_user_logs_ibfk_1')->references(['id'])->on('usr_users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_user_logs', function (Blueprint $table) {
            $table->dropForeign('log_user_logs_ibfk_1');
        });
    }
};
