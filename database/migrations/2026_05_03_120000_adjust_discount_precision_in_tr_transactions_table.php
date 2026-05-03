<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tr_transactions') && Schema::hasColumn('tr_transactions', 'discount')) {
            DB::statement('ALTER TABLE tr_transactions MODIFY discount DECIMAL(3,2) NULL DEFAULT 0.00');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tr_transactions') && Schema::hasColumn('tr_transactions', 'discount')) {
            DB::statement('ALTER TABLE tr_transactions MODIFY discount DECIMAL(2,2) NULL DEFAULT 0.00');
        }
    }
};
