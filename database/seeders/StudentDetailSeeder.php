<?php

namespace Database\Seeders;

use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $model = StudentDetail::class;
    public function run(): void
    {
        $studentUsers = User::query()
            ->whereHas('privileges', function ($query) {
                $query->where('user_type', 'student');
            })
            ->doesntHave('students')
            ->get();

        foreach ($studentUsers as $user) {
            StudentDetail::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
