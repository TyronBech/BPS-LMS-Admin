<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryFolder extends Model
{
    protected $table = 'gallery_folders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'slug',
        'type',
        'category',
        'description',
        'cover',
        'fb_url',
        'album_date',
        'sort_order',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<GalleryVideo>
     */
    public function videos()
    {
        return $this->hasMany(GalleryVideo::class, 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<GalleryFolder>
     */
    public function children()
    {
        return $this->hasMany(GalleryFolder::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<GalleryFolder, GalleryFolder>
     */
    public function parent()
    {
        return $this->belongsTo(GalleryFolder::class, 'parent_id');
    }
}
