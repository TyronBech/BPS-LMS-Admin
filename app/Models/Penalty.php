<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'transaction_type',
        'penalty_date',
        'penalty_type',
        'status',
        'amount',
    ];
}
