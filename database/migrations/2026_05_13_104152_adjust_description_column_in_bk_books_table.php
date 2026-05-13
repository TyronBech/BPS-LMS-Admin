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
        Schema::table('bk_books', function (Blueprint $table) {
            // Modify description column to JSON format
            // Expected keys: Description, Content notes, Abstract, Reviews, Extent (Required), Acc Material
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};
