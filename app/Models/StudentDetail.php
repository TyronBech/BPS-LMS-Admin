<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'lrn',
        'grade_level',
        'section',
    ];
}
