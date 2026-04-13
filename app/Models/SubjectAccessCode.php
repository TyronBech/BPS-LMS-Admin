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
        'subject_id',
        'access_code',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'bk_subject_access_code_subject',
            'subject_access_code_id',
            'subject_id'
        )->withTimestamps();
    }
}
