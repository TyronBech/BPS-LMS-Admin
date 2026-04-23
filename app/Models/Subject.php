<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bk_subjects';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'ddc',
        'name',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'subject_id', 'id');
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
