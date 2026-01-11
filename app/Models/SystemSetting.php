<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['key', 'value', 'description'];
}
