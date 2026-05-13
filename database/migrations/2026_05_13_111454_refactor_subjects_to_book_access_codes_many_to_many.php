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
        // 1. Create the new pivot table for books and subject access codes
        Schema::create('bk_book_subject_access_code', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('subject_access_code_id');
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('bk_books')->onDelete('cascade');
            $table->foreign('subject_access_code_id', 'fk_book_sac_sac_id')->references('id')->on('bk_subject_access_codes')->onDelete('cascade');
        });

        // 2. Data Migration: Preserve existing relationships if possible
        // Path: Book -> subject_id -> Subject <-> bk_subject_access_code_subject -> subject_access_code_id
        $existingLinks = DB::table('bk_books')
            ->join('bk_subjects', 'bk_books.subject_id', '=', 'bk_subjects.id')
            ->join('bk_subject_access_code_subject', 'bk_subjects.id', '=', 'bk_subject_access_code_subject.subject_id')
            ->select('bk_books.id as book_id', 'bk_subject_access_code_subject.subject_access_code_id')
            ->get();

        foreach ($existingLinks as $link) {
            DB::table('bk_book_subject_access_code')->insert([
                'book_id' => $link->book_id,
                'subject_access_code_id' => $link->subject_access_code_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Drop the subject_id from bk_books
        Schema::table('bk_books', function (Blueprint $table) {
            // Check if foreign key exists first
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'bk_books' AND COLUMN_NAME = 'subject_id' AND TABLE_SCHEMA = DATABASE()");
            if (!empty($foreignKeys)) {
                $table->dropForeign($foreignKeys[0]->CONSTRAINT_NAME);
            }
            $table->dropColumn('subject_id');
        });

        // 4. Drop the subject_id from bk_subject_access_codes if it exists
        if (Schema::hasColumn('bk_subject_access_codes', 'subject_id')) {
            Schema::table('bk_subject_access_codes', function (Blueprint $table) {
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'bk_subject_access_codes' AND COLUMN_NAME = 'subject_id' AND TABLE_SCHEMA = DATABASE()");
                if (!empty($foreignKeys)) {
                    $table->dropForeign($foreignKeys[0]->CONSTRAINT_NAME);
                }
                $table->dropColumn('subject_id');
            });
        }

        // 5. Drop the old pivot table bk_subject_access_code_subject
        Schema::dropIfExists('bk_subject_access_code_subject');

        // 6. Drop bk_subjects table
        Schema::dropIfExists('bk_subjects');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('many', function (Blueprint $table) {
            //
        });
    }
};
