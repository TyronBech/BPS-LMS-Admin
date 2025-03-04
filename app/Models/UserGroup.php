<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $fillable = [
        'name',
        'category',
        'max_book_allowed',
        'borrowed_duration_days',
        'renewal_limit',
        'is_unlimited',
    ];
}
