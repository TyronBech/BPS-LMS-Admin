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
        Schema::create('archive_categories', function (Blueprint $table) {
            $table->bigInteger('archive_id', true);
            $table->bigInteger('category_id');
            $table->string('legend', 12)->nullable();
            $table->string('name', 100);
            $table->integer('previous_inventory')->default(0);
            $table->integer('newly_acquired')->default(0);
            $table->integer('discarded')->default(0);
            $table->integer('present_inventory')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_categories');
    }
};
