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
        Schema::create('tr_transactions', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedBigInteger('user_id')->index('idx_transaction_user_id');
            $table->bigInteger('book_id')->index('tr_transactions_ibfk_2_idx');
            $table->date('reserved_date')->nullable();
            $table->date('pickup_deadline')->nullable();
            $table->date('date_borrowed')->nullable();
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('transaction_type', ['Borrowed', 'Returned', 'Reserved'])->nullable();
            $table->enum('status', ['Borrowed', 'Pending', 'Available for pick up', 'Completed', 'Overdue', 'Cancelled', 'Lost', 'Missing', 'Renew'])->default('Pending');
            $table->string('book_condition', 20)->nullable()->default('Good');
            $table->decimal('penalty_total', 10)->nullable()->default(0);
            $table->enum('penalty_status', ['No Penalty', 'Paid', 'Unpaid', 'Waived'])->nullable()->default('No Penalty');
            $table->text('remarks')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['user_id', 'book_id'], 'idx_transaction_user_id_book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_transactions');
    }
};
