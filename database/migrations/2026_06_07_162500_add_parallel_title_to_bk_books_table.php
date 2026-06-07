<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->text('parallel_title')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropColumn('parallel_title');
        });
    }
};
