<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    protected $table = 'audit_trail';
    protected $primaryKey = 'id';

    public function changedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'record_id', 'id');
    }
    public function visitor() : BelongsTo
    {
        return $this->belongsTo(VisitorDetail::class, 'record_id', 'id');
    }
    public function oldPrivilege() : BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'old_value', 'id');
    }
    public function newPrivilege() : BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'new_value', 'id');
    }
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'record_id', 'id');
    }
    public function oldCategory() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'old_value', 'id');
    }
    public function newCategory() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'new_value', 'id');
    }
    public function transaction() : BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'record_id', 'id');
    }
    public function oldBook() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'old_value', 'id');
    }
    public function newBook() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'new_value', 'id');
    }
    public function oldUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'old_value', 'id');
    }
    public function newUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'new_value', 'id');
    }
}
