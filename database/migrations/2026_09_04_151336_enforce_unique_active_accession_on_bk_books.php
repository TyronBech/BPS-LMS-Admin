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
            $table->dropUnique('bk_books_accession_unique');
            $table->dropUnique(['active_accession']);
            $table->dropColumn('active_accession');
        });

        Schema::table('bk_books', function (Blueprint $table) {
            $table->string('active_accession', 20)->nullable()->virtualAs('CASE WHEN deleted_at IS NULL THEN accession ELSE NULL END');
            $table->unique('active_accession');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropUnique(['active_accession']);
            $table->dropColumn('active_accession');
            $table->unique('accession', 'bk_books_accession_unique');
        });

        Schema::table('bk_books', function (Blueprint $table) {
            $table->string('active_accession', 20)->nullable();
            $table->unique('active_accession');
        });
    }
};
