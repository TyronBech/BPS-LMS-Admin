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
        Schema::table('tr_transactions', function (Blueprint $table) {
            $table->foreign(['user_id'], 'tr_transactions_ibfk_1')->references(['id'])->on('usr_users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['book_id'], 'tr_transactions_ibfk_2')->references(['id'])->on('bk_books')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_transactions', function (Blueprint $table) {
            $table->dropForeign('tr_transactions_ibfk_1');
            $table->dropForeign('tr_transactions_ibfk_2');
        });
    }
};
