<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookAudit extends Model
{
    protected $table = 'book_audit';
    protected $fillable = [
        'book_id',
        'file_changed',
        'old_value',
        'new_value',
        'change_type',
        'changed_by',
        'changed_date',
    ];
}
