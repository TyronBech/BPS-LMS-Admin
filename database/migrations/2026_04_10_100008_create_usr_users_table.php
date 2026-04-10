<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usr_users', function (Blueprint $table) {
            $table->id();
            $table->string('rfid', 20)->nullable();
            $table->bigInteger('privilege_id')->unsigned()->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 10)->nullable();
            $table->enum('gender', ['Male','Female','Prefer not to say']);
            $table->string('profile_image')->nullable();
            $table->string('email', 50)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('two_factor_enabled')->default('0');
            $table->string('two_factor_secret')->nullable();
            $table->text('two_factor_backup_codes')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
            $table->string('active_email', 50)->nullable();
            $table->string('active_rfid', 20)->nullable();
            $table->unique('active_email');
            $table->unique('active_rfid');
            $table->index('privilege_id');
            $table->foreign('privilege_id')->references('id')->on('privileges')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usr_users');
    }
};