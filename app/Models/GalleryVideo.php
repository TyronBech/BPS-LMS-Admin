<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryVideo extends Model
{
    protected $table = 'gallery_videos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'folder_id',
        'title',
        'description',
        'url',
        'thumbnail'
    ];

    public function folder()
    {
        return $this->belongsTo(GalleryFolder::class, 'folder_id');
    }
}
