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
                'key' => 'reservation_system_active',
                'value' => 'true',
                'description' => 'Indicates if the reservation system is active.',
            ],
            [
                'key' => 'inventory_cycle_active',
                'value' => '0',
                'description' => 'Tracks whether the book inventory cycle is currently active.',
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
