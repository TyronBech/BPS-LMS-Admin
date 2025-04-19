<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends Model
{
    use SoftDeletes;
    protected $table = 'privileges';
    protected $fillable = [
        'user_type',
        'category',
        'max_book_allowed',
        'borrow_duration_days',
        'renewal_limit',
    ];
    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'privilege_id', 'id');
    }
}
