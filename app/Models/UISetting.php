<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class UISetting extends Model
{
    use SoftDeletes;
    protected $table = 'ui_settings';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'org_name',
        'org_initial',
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

    /**
     * Get the org_logo as a base64 data URL for display
     */
    public function getOrgLogoBase64Attribute()
    {
        Log::info('Accessing org_logo attribute');
        if (!$this->org_logo) {
            Log::warning('org_logo is null');
            return null;
        }
        Log::info('org_logo accessed successfully');
        return 'data:image/png;base64,' . $this->org_logo;
    }

    /**
     * Get the org_logo_full as a base64 data URL for display
     */
    public function getOrgLogoFullBase64Attribute()
    {
        Log::info('Accessing org_logo_full attribute');
        if (!$this->org_logo_full) {
            Log::warning('org_logo_full is null');
            return null;
        }
        Log::info('org_logo_full accessed successfully');
        return 'data:image/png;base64,' . $this->org_logo_full;
    }
}
