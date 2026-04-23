<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bk_subjects', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropIndex(['book_id']);
            $table->dropColumn('book_id');
        });
    }

    public function down(): void
    {
        Schema::table('bk_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable()->after('id');
        });

        DB::table('bk_books')
            ->whereNotNull('subject_id')
            ->orderBy('id')
            ->get(['id', 'subject_id'])
            ->each(function ($book) {
                DB::table('bk_subjects')
                    ->where('id', $book->subject_id)
                    ->update(['book_id' => $book->id]);
            });

        Schema::table('bk_subjects', function (Blueprint $table) {
            $table->index('book_id');
            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
        });
    }
};
