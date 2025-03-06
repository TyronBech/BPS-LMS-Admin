<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
