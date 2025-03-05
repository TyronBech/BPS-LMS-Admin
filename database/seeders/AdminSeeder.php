<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try{
            $user1 = new \App\Models\StagingUser();
            $user1->rfid = "1234567890";
            $user1->first_name = "Tyron";
            $user1->middle_name = "Panti";
            $user1->last_name = "Bechayda";
            $user1->suffix = "";
            $user1->email = "test@example.com";
            $user1->password = Hash::make('null');
            $user1->profile_image = "default.jpg";
            $user1->penalty_total = 0;
            $user1->user_type = "Faculty";
            $user1->group_name = "Part-time Faculty";
            $user1->lrn = "";
            $user1->grade_level = "";
            $user1->section = "";
            $user1->employee_id = "1234567890";
            $user1->visitor_id = "";
            $user1->gender = "Male";
            $user1->school_org = "";
            $user1->purpose = "";
            $user1->save();
            // user
            $user2 = new \App\Models\StagingUser();
            $user2->rfid = "0000000000";
            $user2->first_name = "Gecilie";
            $user2->middle_name = "Caaya";
            $user2->last_name = "Almirañez";
            $user2->suffix = "";
            $user2->email = "sample@gmail.com";
            $user2->password = Hash::make('null');
            $user2->profile_image = "default.jpg";
            $user2->penalty_total = 0;
            $user2->user_type = "Faculty";
            $user2->group_name = "Part-time Faculty";
            $user2->lrn = "";
            $user2->grade_level = "";
            $user2->section = "";
            $user2->employee_id = "0000000000";
            $user2->visitor_id = "";
            $user2->gender = "Female";
            $user2->school_org = "";
            $user2->purpose = "";
            $user2->save();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
        }
        DB::commit();
        DB::beginTransaction();
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
        }
        DB::commit();    
        // admin
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
