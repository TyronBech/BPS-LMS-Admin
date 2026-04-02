<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes, HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'bk_inventories';
    public $timestamps = true;
    protected $attributes = [
        'is_scanned' => 0,
    ];
    protected $fillable = [
        'book_id',
        'is_scanned',
        'checked_at',
    ];
    protected $casts = [
        'is_scanned' => 'boolean',
        'checked_at' => 'datetime',
    ];
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id')->withTrashed();
    }
}
