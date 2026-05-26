<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Link the 4 specific users to the role of 'Super Admin' in roles and permissions
        $emails = [
            'tyronbechayda1112@gmail.com',
            'jcormita000@gmail.com',
            'christiangomelanfog123@gmail.com',
            'princessryanramos29@gmail.com',
        ];
        DB::statement('CALL DistributeStagingUsers()');
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Link them to the role of super admin in roles and permission
                $user->syncRoles(['Super Admin']);
                $this->command->info("Assigned Super Admin role to {$email}");
            } else {
                $this->command->warn("User {$email} not found. Make sure they are seeded first.");
            }
        }
    }
}
