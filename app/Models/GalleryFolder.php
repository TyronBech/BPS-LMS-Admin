<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryFolder extends Model
{
    protected $table = 'gallery_folders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'cover'
    ];

    public function videos()
    {
        return $this->hasMany(GalleryVideo::class, 'folder_id');
    }

    public function children()
    {
        return $this->hasMany(GalleryFolder::class, 'parent_id');
    }
}
