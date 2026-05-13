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
        Schema::table('bk_books', function (Blueprint $table) {
            // Rename 'author' to 'authors' and change type to JSON
            // Expected keys: Main author, Added authors, Contributors, Corporate author
            $table->renameColumn('author', 'authors');
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
