<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bk_books';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'accession',
        'call_number',
        'title',
        'authors',
        'description',
        'edition',
        'isbn',
        'place_of_publication',
        'publisher',
        'copyrights',
        'remarks',
        'category_id',
        'cover_image',
        'digital_copy_url',
        'barcode',
        'book_type',
        'availability_status',
        'condition_status',
    ];
    protected $casts = [
        'description' => 'array',
        'authors' => 'array',
    ];

    public static function getTableName()
    {
        return (new self())->getTable();
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'book_id', 'id');
    }
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'book_id', 'id');
    }

    public function subjectAccessCodes(): BelongsToMany
    {
        return $this->belongsToMany(SubjectAccessCode::class, 'bk_book_subject_access_code', 'book_id', 'subject_access_code_id')->withTimestamps();
    }
}
