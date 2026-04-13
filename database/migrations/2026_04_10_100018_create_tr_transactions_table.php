<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('book_id')->unsigned();
            $table->date('reserved_date')->nullable();
            $table->date('pickup_deadline')->nullable();
            $table->date('date_borrowed')->nullable();
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('transaction_type', ['Borrowed','Returned','Reserved'])->nullable();
            $table->enum('status', ['Borrowed','Pending','Available for pick up','Completed','Overdue','Cancelled','Lost','Missing','Renew'])->default('pending');
            $table->string('book_condition', 20)->nullable()->default('good');
            $table->decimal('penalty_total', 10,2)->nullable()->default('0.00');
            $table->enum('penalty_status', ['No Penalty','Paid','Unpaid','Waived'])->nullable()->default('no penalty');
            $table->text('remarks')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('deleted_at')->nullable();
            $table->index('user_id');
            $table->index('book_id');
            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('usr_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr_transactions');
    }
};