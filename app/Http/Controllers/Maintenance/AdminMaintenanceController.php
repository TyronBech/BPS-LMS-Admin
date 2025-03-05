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
        $admins = User::all();
        $roles = Role::all();
        return view('maintenance.admins.admins', compact('admins', 'roles'));
    }
    public function create()
    {
        $roles = Role::where('guard_name', 'admin')
                    ->where('name', '!=', 'Super Admin')
                    ->get();
        return view('maintenance.admins.create', compact('roles'));
    }
    public function store(Request $request){
        $request->validate([
            'first-name'    => 'required|string|max:50',            
            'middle-name'   => 'required|string|max:50',
            'last-name'     => 'required|string|max:50',
            'email'         => 'required|email',            
            'password'      => 'required|min:8',
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
            $admin = User::create([
                'first_name'    => $request->input('first-name'),
                'middle_name'   => $request->input('middle-name'),
                'last_name'     => $request->input('last-name'),
                'email'         => $request->input('email'),
                'password'      => Hash::make($request->input('password')),
            ]);
            $admin->assignRole(Role::findById($request->input('role'), 'admin'));
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
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
                    ->where('name', '!=', 'Super Admin')
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
