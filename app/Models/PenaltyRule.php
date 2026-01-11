<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenaltyRule extends Model
{
    use SoftDeletes;

    protected $table = 'penalty_rules';
    protected $primaryKey = 'id';
    public $timestamps = true;
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
