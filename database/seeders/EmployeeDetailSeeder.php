<?php

namespace Database\Seeders;

use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeDetailSeeder extends Seeder
{
  public function run(): void
  {
    $employeeUsers = User::query()
      ->with('privileges')
      ->whereHas('privileges', function ($query) {
        $query->where('user_type', 'employee');
      })
      ->doesntHave('employees')
      ->get();

    foreach ($employeeUsers as $user) {
      EmployeeDetail::factory()->create([
        'user_id' => $user->id,
        'employee_role' => $user->privileges->category ?? 'Staff',
      ]);
    }
  }
}
