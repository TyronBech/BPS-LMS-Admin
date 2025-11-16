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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign(['transaction_id'], 'notifications_ibfk_transaction')->references(['id'])->on('tr_transactions')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['user_id'], 'notifications_ibfk_user')->references(['id'])->on('usr_users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_ibfk_transaction');
            $table->dropForeign('notifications_ibfk_user');
        });
    }
};
