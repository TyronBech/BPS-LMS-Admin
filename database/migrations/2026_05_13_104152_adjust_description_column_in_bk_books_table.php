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
            // First, convert existing text data to JSON format to avoid "Invalid JSON text" errors
            DB::table('bk_books')->whereNotNull('description')->get()->each(function ($book) {
                // Skip if it looks like JSON already
                if (str_starts_with(trim($book->description), '{') || str_starts_with(trim($book->description), '[')) {
                    return;
                }
                
                $jsonDescription = json_encode([
                    'Description' => $book->description,
                    'Content notes' => null,
                    'Abstract' => null,
                    'Reviews' => null,
                    'Extent' => 'N/A', // Required key
                    'Acc Material' => null
                ]);

                DB::table('bk_books')->where('id', $book->id)->update(['description' => $jsonDescription]);
            });

            // Modify description column to JSON format
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};
