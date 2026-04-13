<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->inRandomOrder()->limit(3)->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $transactionId = Transaction::query()->value('id');

        foreach ($userIds as $userId) {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'transaction_id' => $transactionId,
                'title' => 'Seeder Notification',
                'message' => 'This is a seeded notification entry.',
                'type' => 'info',
                'notif_date' => now(),
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
