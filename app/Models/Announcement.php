<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'priority',
        'date',
        'is_featured',
        'is_published',
        'quote',
        'image'
    ];
}
