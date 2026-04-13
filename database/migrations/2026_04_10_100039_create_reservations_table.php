<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('book_id');
            $table->date('reservation_date');
            $table->enum('status', ['Active', 'Completed', 'Cancelled']);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('book_id');
            $table->foreign('user_id')->references('id')->on('usr_users')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
