<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bk_book_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('bk_subjects')->onDelete('cascade');
        });

        // Migrate existing data
        $books = DB::table('bk_books')->whereNotNull('subject_id')->get(['id', 'subject_id']);
        foreach ($books as $book) {
            DB::table('bk_book_subject')->insert([
                'book_id' => $book->id,
                'subject_id' => $book->subject_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the column after data migration
        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('bk_subjects')->nullOnDelete();
        });

        // Try to recover data from pivot
        $pivots = DB::table('bk_book_subject')->get(['book_id', 'subject_id']);
        foreach ($pivots as $pivot) {
            DB::table('bk_books')->where('id', $pivot->book_id)->update(['subject_id' => $pivot->subject_id]);
        }

        Schema::dropIfExists('bk_book_subject');
    }
};
