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
            $table->enum('educational_level', ['elementary', 'junior high school', 'senior high school'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_categories', function (Blueprint $table) {
            $table->dropColumn('educational_level');
        });
    }
};
