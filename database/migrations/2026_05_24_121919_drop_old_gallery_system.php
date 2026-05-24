<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old gallery system tables
        Schema::dropIfExists('gallery_videos');
        Schema::dropIfExists('gallery_folders');
    }

    public function down(): void
    {
        // Recreate the old gallery system if rollback is needed
        Schema::create('gallery_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('gallery_folders')->onDelete('cascade');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('slug');
            $table->enum('type', ['folder', 'album'])->default('folder');
            $table->enum('category', ['photo', 'video'])->default('video');
            $table->text('description')->nullable();
            $table->string('fb_url')->nullable();
            $table->string('album_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'slug']);
        });

        DB::statement('ALTER TABLE gallery_folders ADD cover MEDIUMBLOB AFTER description');

        Schema::create('gallery_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('gallery_folders')->onDelete('cascade');
            $table->string('title');
            $table->string('url');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('folder_id');
        });
    }
};
