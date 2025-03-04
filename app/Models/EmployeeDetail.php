<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $table = 'employee_details';

    protected $fillable = [
        'user_id',
        'employee_id',
    ];
}
