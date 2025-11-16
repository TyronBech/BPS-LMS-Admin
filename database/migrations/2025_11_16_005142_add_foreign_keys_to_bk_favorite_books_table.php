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
        Schema::table('bk_favorite_books', function (Blueprint $table) {
            $table->foreign(['book_id'], 'fk_favorite_book')->references(['id'])->on('bk_books')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_favorite_user')->references(['id'])->on('usr_users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_favorite_books', function (Blueprint $table) {
            $table->dropForeign('fk_favorite_book');
            $table->dropForeign('fk_favorite_user');
        });
    }
};
