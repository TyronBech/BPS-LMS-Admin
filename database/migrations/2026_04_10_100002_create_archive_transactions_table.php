<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_transactions', function (Blueprint $table) {
            $table->bigInteger('archive_id')->unsigned()->autoIncrement();
            $table->bigInteger('transaction_id')->unsigned();
            $table->enum('transaction_type', ['Borrowed','Returned','Reserved'])->nullable();
            $table->enum('status', ['Borrowed','Pending','Completed','Overdue','Cancelled','Lost','Missing'])->nullable();
            $table->date('date_borrowed')->nullable();
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('book_condition', 20)->nullable();
            $table->decimal('penalty_total', 10,2)->nullable();
            $table->enum('penalty_status', ['No Penalty','Paid','Unpaid','Waived'])->nullable();
            $table->text('transaction_remarks')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('rfid', 20)->nullable();
            $table->string('full_name')->nullable();
            $table->enum('gender', ['Male','Female','Prefer not to say'])->nullable();
            $table->string('user_type', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->bigInteger('book_id')->unsigned()->nullable();
            $table->string('book_category_name', 100)->nullable();
            $table->string('accession', 50)->nullable();
            $table->string('call_number', 50)->nullable();
            $table->string('title', 150)->nullable();
            $table->text('author')->nullable();
            $table->string('edition', 50)->nullable();
            $table->enum('availability_status', ['Available','Borrowed','In Use','Reserved'])->nullable();
            $table->enum('condition_status', ['New','Good','Fair','Poor'])->nullable();
            $table->dateTime('user_time_in')->nullable();
            $table->dateTime('user_time_out')->nullable();
            $table->string('notif_title', 100)->nullable();
            $table->text('notif_message')->nullable();
            $table->timestamp('archived_at')->nullable()->useCurrent();
            $table->primary('archive_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_transactions');
    }
};