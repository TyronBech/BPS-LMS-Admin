<?php

namespace Database\Seeders;

use App\Models\UISetting;
use Illuminate\Database\Seeder;

class UISettingSeeder extends Seeder
{
  public function run(): void
  {
    $defaultLogoBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/w8AAoMBgA4q6WkAAAAASUVORK5CYII=';

    UISetting::updateOrCreate(
      ['id' => 1],
      [
        'org_name' => 'BPSU Library',
        'org_initial' => 'BPSU',
        'org_address' => 'Bataan Peninsula State University',
        'org_logo' => $defaultLogoBase64,
        'org_logo_full' => $defaultLogoBase64,
        'email' => 'library@bpsu.edu.ph',
        'contact_number' => '+63-000-000-0000',
        'social_links' => [
          'facebook' => null,
          'instagram' => null,
          'x' => null,
        ],
        'theme_colors' => [
          'primary' => '#0D6EFD',
          'secondary' => '#6C757D',
        ],
      ]
    );
  }
}
