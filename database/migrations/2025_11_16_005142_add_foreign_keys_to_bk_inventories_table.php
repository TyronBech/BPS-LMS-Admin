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
        Schema::table('bk_inventories', function (Blueprint $table) {
            $table->foreign(['book_id'], 'bk_inventories_ibfk_1')->references(['id'])->on('bk_books')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_inventories', function (Blueprint $table) {
            $table->dropForeign('bk_inventories_ibfk_1');
        });
    }
};
