<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('bk_books')->whereNull('isbn')->update(['isbn' => 'N/A']);

        Schema::table('bk_books', function (Blueprint $table) {
            $table->string('isbn', 50)->nullable(false)->default('N/A')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->string('isbn', 50)->nullable()->change();
        });
    }
};
