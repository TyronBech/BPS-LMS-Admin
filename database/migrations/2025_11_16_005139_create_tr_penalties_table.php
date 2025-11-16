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
        Schema::create('tr_penalties', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('transaction_id')->index('fk_transaction_id');
            $table->bigInteger('penalty_rule_id')->index('fk_penalty_rule_id');
            $table->decimal('amount', 10);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_penalties');
    }
};
