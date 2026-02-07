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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('table_name')->nullable()->index('table_name_index');
            $table->enum('sync_status', ['success', 'error', 'pending'])->default('pending')->index('sync_status_index');
            $table->integer('sync_duration')->nullable()->comment('Duration in seconds');
            $table->longText('error_message')->nullable();
            $table->dateTime('synced_at')->nullable()->index('synced_at_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
