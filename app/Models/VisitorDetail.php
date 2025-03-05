<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorDetail extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_id',
        'school_org',
        'purpose',
        'gender',
    ];
    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
