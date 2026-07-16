<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SubjectAccessCode;
use Illuminate\Database\Seeder;

class SubjectAccessCodeSeeder extends Seeder
{
  public function run(): void
  {
    $subjects = Subject::query()->get();

    if ($subjects->isEmpty()) {
      return;
    }

    $codePool = [
      'GEN-READ',
      'KNW-001',
      'PHL-101',
      'RLG-201',
      'SOC-301',
      'LAN-401',
      'SCI-501',
      'LAB-ACCESS',
      'TEC-601',
      'ART-701',
      'LIT-801',
      'HIS-901',
    ];

    foreach ($subjects as $subject) {
      $sampledCodes = collect($codePool)->shuffle()->take(random_int(1, 3));

      foreach ($sampledCodes as $code) {
        $accessCode = SubjectAccessCode::query()
          ->whereRaw('LOWER(access_code) = ?', [strtolower($code)])
          ->first();

        if (!$accessCode) {
          $accessCode = SubjectAccessCode::factory()->create([
            'subject_id' => $subject->id,
            'access_code' => $code,
          ]);
        }

        $subject->accessCodes()->syncWithoutDetaching([$accessCode->id]);
      }
    }
  }
}
