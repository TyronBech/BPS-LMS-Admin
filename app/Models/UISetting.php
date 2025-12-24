<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UISetting extends Model
{
    use SoftDeletes;
    protected $table = 'ui_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'org_name',
        'org_address',
        'org_logo',
        'org_logo_full',
        'email',
        'contact_number',
        'social_links',
        'theme_colors',
    ];

    protected $casts = [
        'social_links' => 'array',
        'theme_colors' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
