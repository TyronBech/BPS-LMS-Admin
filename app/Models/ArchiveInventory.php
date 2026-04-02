<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveInventory extends Model
{
    protected $table = 'archive_inventories';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $fillable = [
        'book_id',
        'accession',
        'call_number',
        'title',
        'author',
        'remarks',
        'checked_at',
        'archived_at',
    ];
    protected $casts = [
        'checked_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
