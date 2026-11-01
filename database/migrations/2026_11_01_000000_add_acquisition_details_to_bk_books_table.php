<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->enum('acquisition_mode', ['Purchase', 'Donation', 'Exchange', 'Gifts'])->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->date('acquisition_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropColumn(['acquisition_mode', 'price', 'acquisition_date']);
        });
    }
};
