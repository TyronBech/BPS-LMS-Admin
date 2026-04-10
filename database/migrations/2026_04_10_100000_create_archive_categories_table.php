<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_categories', function (Blueprint $table) {
            $table->bigInteger('archive_id')->unsigned()->autoIncrement();
            $table->bigInteger('category_id')->unsigned();
            $table->string('legend', 12)->nullable();
            $table->string('name', 100);
            $table->integer('previous_inventory')->default('0');
            $table->integer('newly_acquired')->default('0');
            $table->integer('discarded')->default('0');
            $table->integer('present_inventory')->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('archived_at')->useCurrent();
            $table->primary('archive_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_categories');
    }
};