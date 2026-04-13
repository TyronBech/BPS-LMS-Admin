<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_timezone',
                'value' => 'Asia/Manila',
                'description' => 'Default timezone for scheduled and displayed dates.',
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'description' => 'Maximum login retries before temporary lockout.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
