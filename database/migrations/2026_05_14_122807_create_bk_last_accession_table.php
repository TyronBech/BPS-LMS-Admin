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
        // The accession number follows the format: [Prefix] + [6-digit number]
        // The value that the accession_number is holding is the last accession number used
        // So use the accession_number + 1 to get the next free accession number
        Schema::create('bk_last_accession', function (Blueprint $table) {
            $table->id();
            $table->string('accession_number');
            $table->foreignId('category_id')->constrained('bk_categories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_last_accession');
    }
};
