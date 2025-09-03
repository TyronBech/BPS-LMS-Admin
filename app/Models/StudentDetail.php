<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDetail extends Model
{
    use HasFactory;
    protected $table = 'usr_student_details';
    protected $fillable = [
        'user_id',
        'id_number',
        'level',
        'section',
    ];
    public static function getTableName()
    {
        return (new self())->getTable();
    }
    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
