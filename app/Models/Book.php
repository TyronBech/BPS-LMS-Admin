<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bk_books';
    protected $primaryKey = 'id';
    protected $fillable = [
        'accession',
        'call_number',
        'title',
        'author',
        'edition',
        'place_of_publication',
        'publisher',
        'copyrights',
        'remarks',
        'category_id',
        'cover_image',
        'digital_copy_url',
        'barcode',
        'availability_status',
        'condition_status',
    ];
    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function transactions() : HasMany
    {
        return $this->hasMany(Transaction::class, 'book_id', 'id');
    }
    public function inventory() : HasMany
    {
        return $this->hasMany(Inventory::class, 'book_id', 'id');
    }
}
