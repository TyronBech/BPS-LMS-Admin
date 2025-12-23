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
        Schema::create('archive_inventories', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('book_id');
            $table->string('accession', 50);
            $table->string('call_number', 50)->nullable();
            $table->string('title');
            $table->text('author')->nullable();
            $table->string('remarks', 50)->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('archived_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_inventories');
    }
};
