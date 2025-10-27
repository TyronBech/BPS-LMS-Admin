<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingUser extends Model
{
    protected $table = 'usr_staging_users';
    protected $fillable = [
        'rfid',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'profile_image',
        'user_type',
        'employee_role',
        'id_number',
        'level',
        'section',
        'employee_id',
        'visitor_id',
        'gender',
        'school_org',
        'purpose',
    ];
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
