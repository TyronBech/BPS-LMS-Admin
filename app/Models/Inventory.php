<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'inventories ';
    protected $fillable = [
        'book_id',
        'checked_at',
    ];
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
