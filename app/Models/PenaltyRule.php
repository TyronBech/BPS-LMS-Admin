<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyRule extends Model
{
    protected $table = 'penalty_rules';
    protected $fillable = [
        'type',
        'description',
        'rate',
        'per_day',
    ];

    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'penalty_rule_id', 'id');
    }
}
