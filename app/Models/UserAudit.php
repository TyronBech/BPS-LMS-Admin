<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAudit extends Model
{
    protected $table = 'user_audit';
    protected $fillable = [
        'user_id',
        'file_changed',
        'old_value',
        'new_value',
        'change_type',
        'changed_by',
        'changed_date',
    ];
}
