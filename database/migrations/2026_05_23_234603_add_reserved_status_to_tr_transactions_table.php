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
            $table->enum('status', ['Borrowed', 'Pending', 'Available for pick up', 'Completed', 'Overdue', 'Cancelled', 'Lost', 'Missing', 'Renew', 'Reserved'])->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_transactions', function (Blueprint $table) {
            $table->enum('status', ['Borrowed', 'Pending', 'Available for pick up', 'Completed', 'Overdue', 'Cancelled', 'Lost', 'Missing', 'Renew'])->default('Pending')->change();
        });
    }
};
