<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'transaction_type',
        'date_borrowed',
        'due_date',
        'returned_date',
    ];
}
