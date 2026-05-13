<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectAccessCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bk_subject_access_codes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'access_code',
    ];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'bk_book_subject_access_code', 'subject_access_code_id', 'book_id')->withTimestamps();
    }
}
