<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UISetting extends Model
{
    protected $table = 'ui_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'org_name',
        'org_address',
        'org_logo',
        'org_logo_full',
        'social_links',
        'theme_colors',
    ];

    protected $casts = [
        'social_links' => 'array',
        'theme_colors' => 'array',
    ];
}
