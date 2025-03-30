<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enum\RolesEnum;
use App\Models\User;

class AdminMaintenanceController extends Controller
{
    public function index()
    {
        $admins = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_type', 'App\Models\User')
                    ->where('roles.guard_name', 'admin')
                    ->select('users.*', 'roles.name as role')
                    ->get();
        return view('maintenance.admins.admins', compact('admins'));
    }
    public function create()
    {
        $searched = array();
        $roles = Role::where('guard_name', 'admin')
                    ->get();
        return view('maintenance.admins.create', compact('searched', 'roles'));
    }
    public function search(Request $request){
        $search = strtolower($request->input('user-info'));
        $searched = User::select('first_name', 'middle_name', 'last_name', 'email', 'rfid')
                    ->where('first_name', 'like', '%'.$search.'%')
                    ->where(DB::raw('NOT EXISTS (SELECT 1 FROM model_has_roles WHERE model_has_roles.model_id = users.id)'))
                    ->orWhere('middle_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('rfid', 'like', '%'.$search.'%')
                    ->get();
        $roles = Role::where('guard_name', 'admin')
                    ->get();
        return view('maintenance.admins.create', compact('searched', 'roles'));
    }
    public function store(Request $request){
        if(request()->input('adminID') == null){
            return redirect()->route('maintenance.create-admin')->with('toast-warning', 'Please select an admin');
        }
        if(request()->input('role') == 'None'){
            return redirect()->route('maintenance.create-admin')->with('toast-warning', 'Please select a role');
        }
        DB::beginTransaction();
        try {
            $admin = User::where('rfid', $request->input('adminID'))->first();
            $admin->assignRole(Role::findById($request->input('role')), 'admin');
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('maintenance.create-admin')->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->route('maintenance.admins')->with('toast-success', 'Admin created successfully');
    }
    public function edit(Request $request){
        $admin = null;
        $super_admin = null;
        try{
            $id = array_keys($request->all())[0];
            $admin = User::findOrFail($id);
            $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
            if($authAdmin->hasRole(RolesEnum::SUPER_ADMIN)){
                $super_admin = Role::where('name', 'Super Admin')
                                ->where('guard_name', 'admin')
                                ->first();
            }
            $roles = Role::where('guard_name', 'admin')
                    ->get();
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.admins.edit', compact('admin', 'super_admin', 'roles'));
    }
    public function update(Request $request){
        $request->validate([
            'first-name'    => 'required|string|max:50',            
            'middle-name'   => 'required|string|max:50',
            'last-name'     => 'required|string|max:50',
            'email'         => 'required|email',            
            'role'          => 'required',
        ]);
        if($this->has_invalid_characters($request->input('first-name'))){
            return redirect()->back()->with('toast-warning', 'Admin\'s name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('middle-name'))){
            return redirect()->back()->with('toast-warning', 'Admin\'s middle name contains invalid characters');
        } else if($this->has_invalid_characters($request->input('last-name'))){
            return redirect()->back()->with('toast-warning', 'Admin\'s last name contains invalid characters');
        }
        DB::beginTransaction();
        try {
            $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
            if($authAdmin->hasAnyRole(RolesEnum::SUPER_ADMIN, RolesEnum::ADMIN)){
                $admin = User::findOrFail($request->input('id'));
                $admin->update([
                    'first_name'    => $request->input('first-name'),
                    'middle_name'   => $request->input('middle-name'),
                    'last_name'     => $request->input('last-name'),                
                    'email'         => $request->input('email'),
                ]);
                $admin->syncRoles(Role::findById($request->input('role'), 'admin'));
            } else {
                return redirect()->back()->with('toast-error', 'You do not have permission to modify admin');
            }
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->route('maintenance.admins')->with('toast-success', 'Admin updated successfully');
    }
    public function destroy(Request $request){
        dd($request->all());
        return redirect()->route('maintenance.admins')->with('toast-success', 'you reached this page');
    }
    private function has_invalid_characters($name) {
        $pattern = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ]+$/';
        return !(bool) preg_match($pattern, $name); 
    }
}
