<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('archive_transactions')) {
            Schema::table('archive_transactions', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
        }

        if (Schema::hasTable('archive_inventories')) {
            Schema::table('archive_inventories', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
        }

        // Reset any orphaned books marked as Borrowed or Unreturned that have no active borrowing transaction
        if (Schema::hasTable('bk_books') && Schema::hasTable('tr_transactions')) {
            DB::table('bk_books')
                ->where(function ($query) {
                    $query->where('availability_status', 'Borrowed')
                          ->orWhere('remarks', 'Unreturned');
                })
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('tr_transactions')
                          ->whereColumn('tr_transactions.book_id', 'bk_books.id')
                          ->where('tr_transactions.transaction_type', 'Borrowed')
                          ->whereIn('tr_transactions.status', ['Borrowed', 'Overdue', 'Renew']);
                })
                ->update([
                    'availability_status' => 'Available',
                    'remarks'             => 'On Shelf',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('archive_transactions')) {
            Schema::table('archive_transactions', function (Blueprint $table) {
                $table->string('title', 150)->nullable()->change();
            });
        }

        if (Schema::hasTable('archive_inventories')) {
            Schema::table('archive_inventories', function (Blueprint $table) {
                $table->string('title', 255)->nullable()->change();
            });
        }
    }
};
