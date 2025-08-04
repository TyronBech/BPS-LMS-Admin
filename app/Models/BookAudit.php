<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookAudit extends Model
{
    protected $table = 'aud_book_audit';
    protected $primaryKey = 'id';

    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
    public function changedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
    public function oldCategory() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'old_value', 'id');
    }

    public function newCategory() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'new_value', 'id');
    }
}
