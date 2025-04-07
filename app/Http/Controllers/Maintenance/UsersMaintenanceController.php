<?php

namespace App\Http\Controllers\Maintenance;

use App\Enum\PermissionsEnum;
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
        $users = User::with('students', 'employees', 'groups')
                    ->orderBy('id', 'asc')
                    ->get();
        //dd($users->toArray());
        return view('maintenance.users.users', compact('users'));
    }
    public function create_student()
    {
        return view('maintenance.users.create-student',);
    }
    public function create_employee()
    {
        $groups = UserGroup::where(DB::raw('lower(group_name)'), '!=', 'visitor')
                            ->where(DB::raw('lower(group_name)'), '!=', 'student')
                            ->pluck('group_name');
        return view('maintenance.users.create-employee', compact('groups'));
    }
    public function show(Request $request)
    {
        $search = strtolower($request->input('search-users'));
        $users = User::where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('middle_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('rfid', 'like', '%'.$search.'%')
                    ->orWhereHas('students', function ($q) use ($search) {
                        $q->where('lrn', 'like', '%'.$search.'%')
                          ->orWhere('grade_level', 'like', '%'.$search.'%')
                          ->orWhere('section', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('employees', function ($q) use ($search) {
                        $q->where('employee_id', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('groups', function ($q) use ($search) {
                        $q->where('group_name', 'like', '%'.$search.'%');
                    })
                    ->get();
        return view('maintenance.users.users', compact('users'));
    }
    public function store_student(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'lrn'           => 'sometimes|max:50',
            'grade'         => 'sometimes|max:10',
            'section'       => 'sometimes|max:50',
            'email'         => 'required|email',
            'password'      => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') && strlen($request->input('rfid')) != 10)){
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
        } else if($request->input('lrn') == null || !preg_match('/^[0-9]+$/', $request->input('lrn'))){
            return redirect()->back()->with('toast-warning', 'User\'s LRN is invalid');
        }
        DB::beginTransaction();
        try{
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'lrn'           => $request->input('lrn')           == '' ? null : $request->input('lrn'),
                'grade_level'   => $request->input('grade')         == '' ? null : $request->input('grade'),
                'section'       => $request->input('section')       == '' ? null : $request->input('section'),
                'group_name'    => "Student",
                'email'         => $request->input('email'),
                'password'      => Hash::make($request->input('password')),
                'penalty_total' => 0,
                'user_type'     => "Student",
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage());
        }
        DB::commit();
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function store_employee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'employee_id'   => 'required|string|max:50',
            'group'         => 'required',
            'email'         => 'required|email',
            'password'      => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid')) && strlen($request->input('rfid')) != 10){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } else if(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        }
        DB::beginTransaction();
        try{
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'employee_id'   => $request->input('employee_id'),
                'group_name'    => $request->input('group'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($request->input('password')),
                'penalty_total' => 0,
                'user_type'     => "Employee",
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'User with RFID or email ' . $request->input('rfid') . ' already exists. Error code: ' . $e->getMessage());
        }
        DB::commit();
        try{
            DB::statement('CALL DistributeStagingUsers()');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Error code: ' . $e->getMessage());
        }
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function edit_student(Request $request)
    {
        $user = null;
        try{
            $id = array_keys($request->all())[0];
            $user = User::with('students')->where('id', $id)->first();
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit-student', compact('user'));
    }
    public function edit_employee(Request $request)
    {
        $user = null;
        try{
            $id = array_keys($request->all())[0];
            $user = User::with('employees', 'groups')->where('id', $id)->first();
            $groups = UserGroup::all()->pluck('group_name');
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit-employee', compact('user', 'groups'));
    }
    public function update_student(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'email'         => 'required|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') && strlen($request->input('rfid')) != 10)){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } elseif(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
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
            $studentDetails->update([
                'lrn'           => $request->input('lrn'),
                'grade_level'   => $request->input('grade'),
                'section'       => $request->input('section'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    public function update_employee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|max:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'sometimes|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'sometimes|max:10',
            'email'         => 'required|email',
            'employee_id'   => 'required|string|max:50',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', 'Please fill up the required fields');
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') && strlen($request->input('rfid')) != 10)){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } elseif(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
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
            $employeeDetails = EmployeeDetail::where('user_id', $user->id)->first();
            $employeeDetails->update([
                'employee_id'   => $request->input('employee_id'),
            ]);
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
        $pattern = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/';
        return !(bool) preg_match($pattern, $name); 
    }
}
