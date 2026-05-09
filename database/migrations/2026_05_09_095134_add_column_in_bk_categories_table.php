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
        Schema::table('bk_categories', function (Blueprint $table) {
            $table->enum('category_type', ['Print', 'Non-print', 'E-books'])->after('name')->default('Print');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_categories', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};
