<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'legend',
        'name',
        'previous_inventory',
        'discarded',
        'newly_acquired',
        'present_inventory',

    ];

    public function books() : HasMany
    {
        return $this->hasMany(Book::class, 'category_id', 'id');
    }
}
