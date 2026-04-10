<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr_penalties', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transaction_id')->unsigned();
            $table->bigInteger('penalty_rule_id')->unsigned();
            $table->decimal('amount', 10,2);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('deleted_at')->nullable();
            $table->index('transaction_id');
            $table->index('penalty_rule_id');
            $table->foreign('penalty_rule_id')->references('id')->on('penalty_rules')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('tr_transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr_penalties');
    }
};