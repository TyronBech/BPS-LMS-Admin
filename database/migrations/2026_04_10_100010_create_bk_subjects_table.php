<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_subjects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('book_id')->unsigned()->nullable();
            $table->string('ddc', 50)->nullable();
            $table->string('name');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
            $table->index('book_id');
            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_subjects');
    }
};
