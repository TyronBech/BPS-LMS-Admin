<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_subject_access_codes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subject_id')->unsigned()->nullable();
            $table->string('access_code', 100);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
            $table->index('subject_id');
            $table->index('access_code');
            $table->foreign('subject_id')->references('id')->on('bk_subjects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_subject_access_codes');
    }
};