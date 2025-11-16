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
        Schema::create('bk_categories', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('legend', 12)->nullable();
            $table->string('name', 100)->unique('uniq_categories_name');
            $table->integer('previous_inventory')->default(0);
            $table->integer('newly_acquired')->default(0);
            $table->integer('discarded')->default(0);
            $table->integer('present_inventory')->default(0);
            $table->integer('borrow_duration_days')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_categories');
    }
};
