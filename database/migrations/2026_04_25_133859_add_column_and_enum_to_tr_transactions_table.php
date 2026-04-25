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
        Schema::table('tr_transactions', function (Blueprint $table) {
            $table->enum('penalty_status', ['No Penalty', 'Paid', 'Unpaid', 'Waived', 'Discounted'])->change();
            $table->decimal('discount', 2, 2)->nullable()->default('0.00')->after('penalty_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_transactions', function (Blueprint $table) {
            $table->enum('penalty_status', ['No Penalty', 'Paid', 'Unpaid', 'Waived'])->change();
            $table->dropColumn('discount');
        });
    }
};
