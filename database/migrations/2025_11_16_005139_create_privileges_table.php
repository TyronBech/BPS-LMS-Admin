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
        Schema::create('privileges', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('user_type', 50);
            $table->string('category', 50);
            $table->integer('max_book_allowed')->nullable()->default(1);
            $table->enum('duration_type', ['standard', 'unlimited', 'none'])->default('standard');
            $table->integer('renewal_limit')->nullable()->default(5);
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
        Schema::dropIfExists('privileges');
    }
};
