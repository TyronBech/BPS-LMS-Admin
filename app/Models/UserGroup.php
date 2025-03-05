<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $fillable = [
        'group_name',
        'category',
        'max_book_allowed',
        'borrow_duration_days',
        'renewal_limit',
        'is_unlimited',
        'can_have_role',
    ];
}
