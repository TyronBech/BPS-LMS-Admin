<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->after('category_id');
        });

        DB::table('bk_subjects')
            ->whereNull('deleted_at')
            ->whereNotNull('book_id')
            ->orderBy('id')
            ->get(['id', 'book_id'])
            ->groupBy('book_id')
            ->each(function ($subjects, $bookId) {
                $subject = $subjects->first();

                if ($subject === null) {
                    return;
                }

                DB::table('bk_books')
                    ->where('id', $bookId)
                    ->whereNull('subject_id')
                    ->update(['subject_id' => $subject->id]);
            });

        Schema::table('bk_books', function (Blueprint $table) {
            $table->foreign('subject_id')->references('id')->on('bk_subjects')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `bk_books` MODIFY `barcode` LONGBLOB NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `bk_books` MODIFY `barcode` VARCHAR(255) NULL');
        }

        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
