<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingUser extends Model
{
    protected $fillable = [
        'rfid_tag',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'profile_image',
        'penalty_total',
        'user_type',
        'group_name',
        'lrn',
        'grade_level',
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
