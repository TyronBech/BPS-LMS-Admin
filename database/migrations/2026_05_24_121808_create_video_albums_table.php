<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_albums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('thumbnail')->nullable();
            $table->string('album_date')->nullable();
            $table->string('fb_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('sort_order');
        });

        Schema::create('video_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('video_albums')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('thumbnail')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['album_id', 'slug']);
            $table->index(['album_id', 'sort_order']);
        });

        Schema::create('video_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('video_folders')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('video_provider')->nullable()->comment('youtube, vimeo, google_drive, etc');
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration')->nullable()->comment('duration in seconds');
            $table->integer('sort_order')->default(0);
            $table->integer('play_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['folder_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_items');
        Schema::dropIfExists('video_folders');
        Schema::dropIfExists('video_albums');
    }
};
