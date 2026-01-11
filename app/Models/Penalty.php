<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penalty extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tr_penalties';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'transaction_id',
        'penalty_rule_id',
        'amount',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function penaltyRule()
    {
        return $this->belongsTo(PenaltyRule::class, 'penalty_rule_id', 'id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
