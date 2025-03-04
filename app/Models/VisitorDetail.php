<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorDetail extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_id',
        'school_org',
        'purpose',
        'gender',
    ];
}
