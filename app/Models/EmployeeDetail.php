<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDetail extends Model
{
    protected $table = 'usr_employee_details';

    protected $fillable = [
        'user_id',
        'employee_id',
    ];
    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
