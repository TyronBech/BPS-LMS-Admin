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
        'return_date',
        'status',
        'book_condition',
        'penalty_total',
        'penalty_status',
        'remarks',
    ];
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'transaction_id', 'id');
    }
}
