<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BkNonCirculation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bk_non_circulations';

    protected $fillable = [
        'student_id',
        'faculty_id',
        'subject',
        'borrowed_at',
    ];

    public function student()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }

    public function faculty()
    {
        return $this->belongsTo(EmployeeDetail::class, 'faculty_id');
    }
}
