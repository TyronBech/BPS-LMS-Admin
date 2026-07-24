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
        Schema::table('bk_non_circulations', function (Blueprint $table) {
            $table->dateTime('returned_at')->nullable()->after('borrowed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_non_circulations', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });
    }
};
