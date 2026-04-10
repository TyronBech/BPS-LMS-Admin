<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_settings', function (Blueprint $table) {
            $table->id();
            $table->string('org_name', 100);
            $table->string('org_initial', 45)->nullable();
            $table->text('org_address');
            $table->string('org_logo');
            $table->string('org_logo_full');
            $table->string('email', 100);
            $table->string('contact_number', 45);
            $table->json('social_links')->nullable();
            $table->json('theme_colors')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_settings');
    }
};