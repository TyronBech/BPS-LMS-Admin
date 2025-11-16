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
        Schema::create('aud_transaction_audit', function (Blueprint $table) {
            $table->bigInteger('audit_id', true);
            $table->bigInteger('transaction_id')->index('idx_transaction_id');
            $table->string('field_changed', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->enum('change_type', ['INSERT', 'UPDATE', 'DELETE']);
            $table->string('changed_by', 50)->nullable()->default('system');
            $table->timestamp('changed_at')->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aud_transaction_audit');
    }
};
