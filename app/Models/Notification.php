<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_id',
        'notification_type',
        'notification_date',
        'status',
    ];
}
