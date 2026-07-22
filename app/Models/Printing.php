<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Printing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'printing';

    protected $fillable = [
        'student_id',
        'faculty_id',
        'type',
        'title_of_material',
        'topic',
        'pages',
        'amount',
        'printed_at',
        'visitor_id',
    ];

    public function student()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }

    public function faculty()
    {
        return $this->belongsTo(EmployeeDetail::class, 'faculty_id');
    }

    public function visitor()
    {
        return $this->belongsTo(VisitorDetail::class, 'visitor_id');
    }
}
