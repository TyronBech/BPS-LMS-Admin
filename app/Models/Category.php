<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bk_categories';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'legend',
        'name',
        'category_type',
        'previous_inventory',
        'discarded',
        'newly_acquired',
        'present_inventory',
        'borrow_duration_days',

    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id', 'id');
    }

    public function lastAccession(): HasOne
    {
        return $this->hasOne(BkLastAccession::class, 'category_id', 'id');
    }
}
