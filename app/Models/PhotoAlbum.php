<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoAlbum extends Model
{
    use HasFactory;

    protected $table = 'photo_albums';

    protected $fillable = [
        'name',
        'title',
        'slug',
        'description',
        'thumbnail',
        'album_date',
        'fb_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
