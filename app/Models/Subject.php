<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bk_subjects';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'book_id',
        'ddc',
        'name',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id')->withTrashed();
    }

    public function accessCodes(): BelongsToMany
    {
        return $this->belongsToMany(
            SubjectAccessCode::class,
            'bk_subject_access_code_subject',
            'subject_id',
            'subject_access_code_id'
        )->withTimestamps();
    }
}
