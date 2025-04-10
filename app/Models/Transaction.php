<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'tr_transactions';
    protected $fillable = [
        'user_id',
        'book_id',
        'transaction_type',
        'date_borrowed',
        'due_date',
        'returned_date',
    ];
    public function books() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
