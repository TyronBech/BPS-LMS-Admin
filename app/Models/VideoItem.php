<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoItem extends Model
{
    use HasFactory;

    protected $table = 'video_items';

    protected $fillable = [
        'folder_id',
        'title',
        'slug',
        'description',
        'url',
        'video_provider',
        'thumbnail_url',
        'duration',
        'sort_order',
        'play_count',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->slug)) {
                $item->slug = static::generateUniqueSlug($item->title);
            }
        });

        static::updating(function ($item) {
            if ($item->isDirty('title')) {
                $item->slug = static::generateUniqueSlug($item->title, $item->id);
            }
        });
    }

    public static function generateUniqueSlug($title, $ignoreId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = static::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count++;
            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public function folder()
    {
        return $this->belongsTo(VideoFolder::class, 'folder_id');
    }
}
