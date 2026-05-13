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
        Schema::table('bk_books', function (Blueprint $table) {
            // Rename 'author' to 'authors'
            $table->renameColumn('author', 'authors');
        });

        // Data conversion before type change
        DB::table('bk_books')->whereNotNull('authors')->get()->each(function ($book) {
            // Skip if it looks like JSON already
            if (str_starts_with(trim($book->authors), '{') || str_starts_with(trim($book->authors), '[')) {
                return;
            }
            
            $jsonAuthors = json_encode([
                'Main author' => $book->authors,
                'Added authors' => [],
                'Contributors' => [],
                'Corporate author' => null
            ]);

            DB::table('bk_books')->where('id', $book->id)->update(['authors' => $jsonAuthors]);
        });

        Schema::table('bk_books', function (Blueprint $table) {
            $table->json('authors')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->renameColumn('authors', 'author');
        });

        Schema::table('bk_books', function (Blueprint $table) {
            $table->text('author')->nullable()->change();
        });
    }
};
