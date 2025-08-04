<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionAudit extends Model
{
    protected $table = 'aud_transaction_audit';
    protected $primaryKey = 'aud_id';

    public function transaction() : BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
    public function changedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
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
