<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAudit extends Model
{
    protected $table = 'aud_user_audit';
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }

    public function oldPrivilege()
    {
        return $this->belongsTo(UserGroup::class, 'old_value', 'id');
    }

    public function newPrivilege()
    {
        return $this->belongsTo(UserGroup::class, 'new_value', 'id');
    }
}
