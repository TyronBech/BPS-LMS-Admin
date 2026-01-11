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
        Schema::create('ui_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('org_name', 100);
            $table->string('org_initial', 45)->nullable();
            $table->text('org_address');
            $table->binary('org_logo');
            $table->binary('org_logo_full');
            $table->string('email', 100);
            $table->string('contact_number', 45);
            $table->json('social_links')->nullable();
            $table->json('theme_colors')->nullable();
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
        Schema::dropIfExists('ui_settings');
    }
};
