<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Validator;
use App\Models\StagingUser;
use App\Models\StudentDetail;
use App\Models\EmployeeDetail;

class UsersMaintenanceController extends Controller
{
    public function index()
    {
        $users = User::with('students', 'employees', 'visitors', 'groups')
                    ->orderBy('id', 'asc')
                    ->get();
        //dd($users->toArray());
        return view('maintenance.users.users', compact('users'));
    }
    public function create()
    {
        $groups = UserGroup::all()->pluck('group_name');
        return view('maintenance.users.create', compact('groups'));
    }
    public function show(Request $request)
    {
        $users = User::where('rfid_tag', $request->input('search-users'))->get();
        return view('maintenance.users.users', compact('users'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:50',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'lrn'           => 'sometimes|max:50',
            'grade'         => 'sometimes|max:10',
            'section'       => 'sometimes|max:50',
            'employee_id'   => 'sometimes|max:50',
            'group'         => 'required',
            'email'         => 'required|email',
            'password'      => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if($request->input('group') == 'Student'){
            if($request->input('lrn') == null || $request->input('grade') == null || $request->input('section') == null){
                return redirect()->back()->with('toast-warning', 'Please fill up the fields for students if the user is a student');
            }
            if($request->input('employee_id') != null){
                return redirect()->back()->with('toast-warning', 'Employee ID is not required for students');
            }
        } else {
            if($request->input('lrn') != null || $request->input('grade') != null || $request->input('section') != null){
                return redirect()->back()->with('toast-warning', 'Please remove the fields for students if the user is not a student');
            }
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid'))){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } else if(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        } else if($request->input('grade') != null && !in_array($request->input('grade'), ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s grade is invalid');
        } else if($request->input('section') != null && !preg_match('/^[A-Z]$/', $request->input('section'))){
            return redirect()->back()->with('toast-warning', 'User\'s section contains invalid characters');
        }
        DB::beginTransaction();
        try{
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name') == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')    == '' ? null : $request->input('suffix'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'lrn'           => $request->input('lrn') == '' ? null : $request->input('lrn'),
                'grade_level'   => $request->input('grade')     == '' ? null : $request->input('grade'),
                'section'       => $request->input('section')   == '' ? null : $request->input('section'),
                'employee_id'   => $request->input('employee_id') == '' ? null : $request->input('employeeID'),
                'group_name'    => $request->input('group'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($request->input('password')),
                'penalty_total' => 0,
                'user_type'     => $request->input('group') == 'Student' ? 'student' : 'employee',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage());
        }
        DB::commit();
        DB::beginTransaction();
        try{
            DB::statement('SET SQL_SAFE_UPDATES = 0');
            DB::statement('CALL DistributeStagingUsers()');
            DB::statement('SET SQL_SAFE_UPDATES = 1');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function edit(Request $request)
    {
        $user = null;
        try{
            $id = array_keys($request->all())[0];
            $user = User::with('students', 'employees', 'groups')->where('id', $id)->first();
            $groups = UserGroup::all()->pluck('group_name');
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit', compact('user', 'groups'));
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:50',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'email'         => 'required|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if($request->input('group') == 'Student'){
            if($request->input('lrn') == null || $request->input('grade') == null || $request->input('section') == null){
                return redirect()->back()->with('toast-warning', 'Please fill up the fields for students if the user is a student');
            }
            if($request->input('employee_id') != null){
                return redirect()->back()->with('toast-warning', 'Employee ID is not required for students');
            }
        } else {
            if($request->input('lrn') != null || $request->input('grade') != null || $request->input('section') != null){
                return redirect()->back()->with('toast-warning', 'Please remove the fields for students if the user is not a student');
            }
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid'))){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } elseif(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        } elseif($request->input('grade') != null && !in_array($request->input('grade'), ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s grade is invalid');
        } else if($request->input('section') != null && !preg_match('/^[A-Z]$/', $request->input('section'))){
            return redirect()->back()->with('toast-warning', 'User\'s section contains invalid characters');
        }
        DB::beginTransaction();
        try{
            $user = User::findOrFail($request->input('id'));
            $user->update([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'email'         => $request->input('email'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
            ]);
            $studentDetails = StudentDetail::where('user_id', $user->id)->first();
            $employeeDetails = EmployeeDetail::where('user_id', $user->id)->first();
            if($request->input('group') == 'Student'){
                $studentDetails->update([
                    'lrn'           => $request->input('lrn'),
                    'grade_level'   => $request->input('grade'),
                    'section'       => $request->input('section'),
                ]);
            } else {
                $employeeDetails->update([
                    'employee_id'   => $request->input('employee_id'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $id = $request->input('id');
            User::find($id)->delete();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('delete-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User deleted successfully');
    }
    private function has_invalid_characters($name) {
        $pattern = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ]+$/';
        return !(bool) preg_match($pattern, $name); 
    }
}
