<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VisitorDetail;
use Illuminate\Database\Seeder;

class VisitorDetailSeeder extends Seeder
{
  public function run(): void
  {
    $visitorUsers = User::query()
      ->whereHas('privileges', function ($query) {
        $query->where('user_type', 'visitor');
      })
      ->doesntHave('visitors')
      ->get();

    foreach ($visitorUsers as $user) {
      VisitorDetail::factory()->create([
        'user_id' => $user->id,
      ]);
    }
  }
}
