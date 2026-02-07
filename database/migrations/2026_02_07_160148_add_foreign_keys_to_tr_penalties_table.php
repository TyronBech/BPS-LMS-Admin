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
        Schema::table('tr_penalties', function (Blueprint $table) {
            $table->foreign(['penalty_rule_id'], 'fk_penalty_rule_id')->references(['id'])->on('penalty_rules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['transaction_id'], 'fk_transaction_id')->references(['id'])->on('tr_transactions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_penalties', function (Blueprint $table) {
            $table->dropForeign('fk_penalty_rule_id');
            $table->dropForeign('fk_transaction_id');
        });
    }
};
