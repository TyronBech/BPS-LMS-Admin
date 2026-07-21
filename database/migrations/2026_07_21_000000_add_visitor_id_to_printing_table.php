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
        Schema::table('printing', function (Blueprint $table) {
            $table->foreignId('visitor_id')->nullable()->after('faculty_id')->constrained('usr_visitor_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printing', function (Blueprint $table) {
            $table->dropForeign(['visitor_id']);
            $table->dropColumn('visitor_id');
        });
    }
};
