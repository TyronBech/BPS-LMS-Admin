<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    protected $table = 'privileges';
    protected $fillable = [
        'group_name',
        'category',
        'max_book_allowed',
        'borrow_duration_days',
        'renewal_limit',
        'is_unlimited',
        'can_have_role',
    ];
    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'privilege_id', 'id');
    }
}
