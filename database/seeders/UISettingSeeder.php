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
                'org_name' => 'Bicutan Parochial School, Inc.',
                'org_initial' => 'BPS',
                'org_address' => 'Manuel L. Quezon St. Lower Bicutan, Taguig City',
                'org_logo' => $defaultLogoBase64,
                'org_logo_full' => $defaultLogoBase64,
                'email' => 'learn@bps.edu.ph',
                'contact_number' => '(02) 8252-9613',
                'social_links' => [
                    'facebook' => 'https://web.facebook.com/bicutanparochialschool',
                    'instagram' => 'https://www.instagram.com/explore/locations/279368098/bicutan-parochial-school/',
                    'x' => 'https://x.com/bps_edu_ph',
                    'youtube' => 'https://www.youtube.com/@bicutanparochialschoolinc.4955/featured',
                    'website' => 'https://bps.edu.ph/',
                ],
                'theme_colors' => [
                    'primary' => '#20246b',
                    'secondary' => '#ebf5ff',
                    'tertiary' => '#ffcf01',
                ],
            ]
        );
    }
}
