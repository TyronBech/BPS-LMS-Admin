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
        // 1. Change the column type from enum to string (varchar)
        Schema::table('bk_categories', function (Blueprint $table) {
            $table->string('educational_level', 255)->nullable()->change();
        });

        // 2. Migrate existing lowercase enum values to capitalized JSON arrays
        $categories = DB::table('bk_categories')->get();
        foreach ($categories as $category) {
            if (!empty($category->educational_level)) {
                $level = $category->educational_level;
                
                // If it is already a JSON array, don't re-convert it
                if (str_starts_with($level, '[') && str_ends_with($level, ']')) {
                    continue;
                }

                $mapped = match (strtolower($level)) {
                    'elementary' => ['Elementary'],
                    'junior high school' => ['Junior High School'],
                    'senior high school' => ['Senior High School'],
                    default => [ucwords($level)]
                };
                
                DB::table('bk_categories')
                    ->where('id', $category->id)
                    ->update(['educational_level' => json_encode($mapped)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Convert JSON arrays back to single lowercase string values for the enum
        $categories = DB::table('bk_categories')->get();
        foreach ($categories as $category) {
            if (!empty($category->educational_level)) {
                $level = $category->educational_level;
                $decoded = json_decode($level, true);
                
                if (is_array($decoded) && !empty($decoded)) {
                    $first = strtolower($decoded[0]);
                    $valid = ['elementary', 'junior high school', 'senior high school'];
                    
                    if (in_array($first, $valid)) {
                        DB::table('bk_categories')
                            ->where('id', $category->id)
                            ->update(['educational_level' => $first]);
                    } else {
                        DB::table('bk_categories')
                            ->where('id', $category->id)
                            ->update(['educational_level' => null]);
                    }
                } else {
                    $val = strtolower($level);
                    if (!in_array($val, ['elementary', 'junior high school', 'senior high school'])) {
                        DB::table('bk_categories')
                            ->where('id', $category->id)
                            ->update(['educational_level' => null]);
                    }
                }
            }
        }

        // 2. Change column back to enum
        Schema::table('bk_categories', function (Blueprint $table) {
            $table->enum('educational_level', ['elementary', 'junior high school', 'senior high school'])->nullable()->change();
        });
    }
};
