<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('legend', 12)->nullable();
            $table->string('name', 100);
            $table->integer('previous_inventory')->default('0');
            $table->integer('newly_acquired')->default('0');
            $table->integer('discarded')->default('0');
            $table->integer('present_inventory')->default('0');
            $table->integer('borrow_duration_days')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_categories');
    }
};