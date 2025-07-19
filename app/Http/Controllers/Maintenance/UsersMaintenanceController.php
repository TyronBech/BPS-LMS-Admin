<?php

namespace App\Http\Controllers\Maintenance;

use App\Enum\PermissionsEnum;
use App\Enum\RolesEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Validator;
use App\Models\StagingUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountEmailMessage;

class UsersMaintenanceController extends Controller
{
    public function index()
    {
        $users = User::with('students', 'employees')
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
        $groups = UserGroup::where(DB::raw('lower(category)'), '!=', 'visitor')
                            ->where(DB::raw('lower(category)'), '!=', 'student')
                            ->pluck('category');
        return view('maintenance.users.create-employee', compact('groups'));
    }
    public function show(Request $request)
    {
        $search = strtolower($request->input('search-users'));
        $users = User::where(DB::raw('lower(first_name)'), 'like', '%'.$search.'%')
                    ->orWhere(DB::raw('lower(middle_name)'), 'like', '%'.$search.'%')
                    ->orWhere(DB::raw('lower(last_name)'), 'like', '%'.$search.'%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", middle_name, " ", last_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(middle_name, " ", last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name, " ", middle_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(last_name, ", ", first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(first_name, " ", last_name))'), 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('rfid', 'like', '%'.$search.'%')
                    ->orWhereHas('students', function ($q) use ($search) {
                        $q->where('id_number', 'like', '%'.$search.'%')
                          ->orWhere('level', 'like', '%'.$search.'%')
                          ->orWhere('section', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('employees', function ($q) use ($search) {
                        $q->where('employee_id', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('privileges', function ($q) use ($search) {
                        $q->where('user_type', 'like', '%'.$search.'%');
                    })
                    ->get();
        return view('maintenance.users.users', compact('users'));
    }
    public function store_student(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'nullable|string|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'nullable|string|max:10',
            'gender'        => 'required|in:Male,Female,Prefer not to say',
            'id_number'     => 'required|min:12',
            'level'         => 'required|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') || strlen($request->input('rfid')) != 10)){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } else if(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        } else if($request->input('level') != null && !preg_match('/^(?:[7-9]|1[0-2])$/', $request->input('level'))){
            return redirect()->back()->with('toast-warning', 'User\'s grade level is invalid');
        } else if($request->input('id_number') == null || !preg_match('/^[0-9]+$/', $request->input('id_number'))){
            return redirect()->back()->with('toast-warning', 'User\'s LRN is invalid');
        } else if(User::where('email', $request->input('email'))->exists()){
            return redirect()->back()->with('toast-warning', 'User with email ' . $request->input('email') . ' already exists');
        } else if(User::where('rfid', $request->input('rfid'))->exists()){
            return redirect()->back()->with('toast-warning', 'User with RFID ' . $request->input('rfid') . ' already exists');
        }
        if($request->hasFile('profile-image')){
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            $password = Str::password(8, true, true, true, false);
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'id_number'     => $request->input('id_number'),
                'level'         => $request->input('level'),
                'section'       => $request->input('section'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($password),
                'user_type'     => "student",
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
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
        return redirect()->back()->with('toast-success', 'User added successfully');
    }
    public function store_employee(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'nullable|string|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'nullable|string|max:10',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'employee_id'   => 'required|string|max:50',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid')) || strlen($request->input('rfid')) != 10){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } else if(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        } else if(User::where('email', $request->input('email'))->exists()){
            return redirect()->back()->with('toast-warning', 'User with email ' . $request->input('email') . ' already exists');
        } else if(User::where('rfid', $request->input('rfid'))->exists()){
            return redirect()->back()->with('toast-warning', 'User with RFID ' . $request->input('rfid') . ' already exists');
        }
        if($request->hasFile('profile-image')){
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            $password = Str::password(8, true, true, true, false);
            StagingUser::create([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                'employee_id'   => $request->input('employee_id'),
                'employee_role' => $request->input('employee_role'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($password),
                'user_type'     => "employee",
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
        $this->account_notification(User::where('email', $request->input('email'))->first(), $password);
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
            $user = User::with('employees', 'privileges')->where('id', $id)->first();
            $privileges = UserGroup::where(DB::raw('lower(category)'), '!=', 'visitor')
                        ->where(DB::raw('lower(category)'), '!=', 'student')
                        ->pluck('category');
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.users.edit-employee', compact('user', 'privileges'));
    }
    public function update_student(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'nullable|string|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'nullable|string|max:10',
            'gender'        => 'required|in:Male,Female,Prefer not to say',
            'id_number'     => 'required|min:12',
            'level'         => 'required|min:7|max:12',
            'section'       => 'required|max:50',
            'email'         => 'required|string|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') || strlen($request->input('rfid')) != 10)){
            return redirect()->back()->with('toast-warning', 'RFID number is invalid');
        } else if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s name contains invalid characters');
        } else if($request->input('middle-name') != null && $this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'User\'s last name contains invalid characters');
        } else if(!in_array($request->input('suffix'), ['Jr.', 'Sr.', 'II', 'III', 'IV', ''])){
            return redirect()->back()->with('toast-warning', 'User\'s suffix is invalid');
        } else if($request->input('level') != null && !preg_match('/^(?:[7-9]|1[0-2])$/', $request->input('level'))){
            return redirect()->back()->with('toast-warning', 'User\'s grade level is invalid');
        } else if($request->input('id_number') == null || !preg_match('/^[0-9]+$/', $request->input('id_number'))){
            return redirect()->back()->with('toast-warning', 'User\'s LRN is invalid');
        }
        if($request->hasFile('profile-image')){
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            $student = User::with('students')->where('id', $request->input('id'))->first();
            if($student){
                $student->update([
                    'rfid'          => $request->input('rfid'),
                    'first_name'    => $request->input('first-name'),
                    'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                    'last_name'     => $request->input('last-name'),
                    'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                    'gender'        => $request->input('gender'),
                    'email'         => $request->input('email'),
                    'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
                ]);
                $student->students()->update([
                    'id_number'     => $request->input('id_number'),
                    'level'         => $request->input('level'),
                    'section'       => $request->input('section'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack(); 
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User updated successfully');
    }
    public function update_employee(Request $request)
    {
        $users = new User();
        $validator = Validator::make($request->all(), [
            'rfid'          => 'required|string|min:10',
            'first-name'    => 'required|string|max:50',
            'middle-name'   => 'nullable|string|max:50',
            'last-name'     => 'required|string|max:50',
            'suffix'        => 'nullable|string|max:10',
            'gender'        => 'required|in:' . implode(',', $this->extract_enums($users->getTable(), 'gender')),
            'employee_id'   => 'required|string|max:50',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'email'         => 'required|string|email',
        ]);
        if($validator->fails()){
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        if(!preg_match('/^[0-9]+$/', $request->input('rfid') || strlen($request->input('rfid')) != 10)){
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
        if($request->hasFile('profile-image')){
            $image = $request->file('profile-image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['profile-image' => $base64Image]);
        }
        DB::beginTransaction();
        try{
            $employee = User::with('employees')->where('id', $request->input('id'))->first();
            $employee->update([
                'rfid'          => $request->input('rfid'),
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name')   == '' ? null : $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'suffix'        => $request->input('suffix')        == '' ? null : $request->input('suffix'),
                'gender'        => $request->input('gender'),
                'email'         => $request->input('email'),
                'profile_image' => $request->input('profile-image') == '' ? null : $request->input('profile-image'),
            ]);
            $employee->employees()->update([
                'employee_id'   => $request->input('employee_id'),
                'employee_role' => $request->input('employee_role'),
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
        try {
            $id = $request->input('id');
            $user = User::findOrFail($id); // Throws ModelNotFoundException if not found

            if ($user->hasRole(RolesEnum::SUPER_ADMIN)) {
                DB::rollBack(); // Rollback transaction before redirecting
                return redirect()->back()->with('delete-error', 'Cannot delete a super admin user');
            }

            $user->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'User not found.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'A database error occurred while deleting the user.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('delete-error', 'An unexpected error occurred while deleting the user.');
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'User deleted successfully');
    }
    private function account_notification($user, $password){
        Mail::to($user->email)->send(new AccountEmailMessage($user, $password));
    }
    private function extract_enums($table, $columnName){
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }   
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        return $enumValues;
    }
    private function has_invalid_characters($name) {
        $pattern = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/';
        return !(bool) preg_match($pattern, $name); 
    }
}
