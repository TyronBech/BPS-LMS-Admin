<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Illuminate\Support\Facades\Schema;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $model = Reservation::class;
    public function run(): void
    {
        if (!Schema::hasTable((new Reservation())->getTable())) {
            return;
        }

        Reservation::factory()->count(10)->create();
    }
}
