<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;
    protected $table = 'user_logs';
    protected $fillable = [
        'user_id',
        'computer_use',
        'timestamp',
        'action',
    ];
}
