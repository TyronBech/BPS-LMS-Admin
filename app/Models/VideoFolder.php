<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoFolder extends Model
{
    use HasFactory;

    protected $table = 'video_folders';

    protected $fillable = [
        'album_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function album()
    {
        return $this->belongsTo(VideoAlbum::class, 'album_id');
    }

    public function items()
    {
        return $this->hasMany(VideoItem::class, 'folder_id');
    }
}
