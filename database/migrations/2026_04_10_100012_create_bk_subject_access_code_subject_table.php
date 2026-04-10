<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_subject_access_code_subject', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subject_id')->unsigned();
            $table->bigInteger('subject_access_code_id')->unsigned();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index('subject_access_code_id');
            $table->foreign('subject_id')->references('id')->on('bk_subjects')->onDelete('cascade');
            $table->foreign('subject_access_code_id')->references('id')->on('bk_subject_access_codes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_subject_access_code_subject');
    }
};