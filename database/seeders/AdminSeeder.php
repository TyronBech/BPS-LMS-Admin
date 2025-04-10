<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StagingUser;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $admin = new \App\Models\Admin();
        // $admin->name = 'Tyron P. Bechayda';
        // $admin->email = 'test@example.com';
        // $admin->password = Hash::make('null');
        // $admin->created_at = now();
        // $admin->updated_at = now();
        // $admin->save();
        // $admin1 = new \App\Models\Admin();
        // $admin1->name = 'Gecilie C. Almirañez';
        // $admin1->email = 'sample@gmail.com';
        // $admin1->password = Hash::make('null');
        // $admin1->created_at = now();
        // $admin1->updated_at = now();
        // $admin1->save();

        // user group
        // $group = new \App\Models\UserGroup();
        // $group->group_name = "Part-time Faculty";
        // $group->category = "Faculty";
        // $group->max_book_allowed = 5;
        // $group->borrow_duration_days = 7;
        // $group->renewal_limit = 2;
        // $group->is_unlimited = true;
        // $group->can_have_role = true;
        // $group->save();
        // staging user
        // DB::beginTransaction();
        // try{
        //     $user1 = new StagingUser();
        //     $user1->rfid = "1234567890";
        //     $user1->first_name = "Tyron";
        //     $user1->middle_name = "Panti";
        //     $user1->last_name = "Bechayda";
        //     $user1->suffix = null;
        //     $user1->email = "superadmin@gmail.com";
        //     $user1->password = Hash::make('Super@admin');
        //     $user1->profile_image = null;
        //     $user1->user_type = "employee";
        //     $user1->employee_role = "Part-time Faculty";
        //     $user1->id_number = null;
        //     $user1->level = null;
        //     $user1->section = null;
        //     $user1->employee_id = "1234567890";
        //     $user1->gender = "Male";
        //     $user1->school_org = null;
        //     $user1->purpose = null;
        //     $user1->save();
        //     // user
        //     $user2 = new StagingUser();
        //     $user2->rfid = "1029384756";
        //     $user2->first_name = "Gecilie";
        //     $user2->middle_name = "Caaya";
        //     $user2->last_name = "Almirañez";
        //     $user2->suffix = null;
        //     $user2->email = "admin@gmail.com";
        //     $user2->password = Hash::make('Admin@admin');
        //     $user2->profile_image = null;
        //     $user2->user_type = "employee";
        //     $user2->employee_role = "Part-time Faculty";
        //     $user2->id_number = null;
        //     $user2->level = null;
        //     $user2->section = null;
        //     $user2->employee_id = "1029384756";
        //     $user2->gender = "Female";
        //     $user2->school_org = null;
        //     $user2->purpose = null;
        //     $user2->save();
        // } catch(\Illuminate\Database\QueryException $e){
        //     Log::error($e->getMessage());
        //     DB::rollBack();
        // }
        // DB::commit();
        //calling procedure
        // try{
        //     DB::statement('CALL DistributeStagingUsers()');
        // } catch(\Illuminate\Database\QueryException $e){
        //     Log::error($e->getMessage());
        //     DB::rollBack();
        // }
        //admin
        // $admin = new \App\Models\User();
        // $admin->name = 'Tyron P. Bechayda';
        // $admin->email = 'test@example.com';
        // $admin->password = Hash::make('null');
        // $admin->created_at = now();
        // $admin->updated_at = now();
        // $admin->save();
        // $admin1 = new \App\Models\User();
        // $admin1->name = 'Gecilie C. Almirañez';
        // $admin1->email = 'sample@gmail.com';
        // $admin1->password = Hash::make('null');
        // $admin1->created_at = now();
        // $admin1->updated_at = now();
        // $admin1->save();
    }
}
