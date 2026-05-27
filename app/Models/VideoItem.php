<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoItem extends Model
{
    use HasFactory;

    protected $table = 'video_items';

    protected $fillable = [
        'folder_id',
        'title',
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

    public function folder()
    {
        return $this->belongsTo(VideoFolder::class, 'folder_id');
    }
}
