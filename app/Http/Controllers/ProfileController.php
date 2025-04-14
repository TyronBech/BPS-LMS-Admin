<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmployeeDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        return view('profile.index', compact('user'));
    }
    public function update(Request $request)
    {
        $rules = [
            'first_name'    => ['required', 'string', 'max:50'],
            'middle_name'   => ['required', 'string', 'max:50'],
            'last_name'     => ['required', 'string', 'max:50'],
            'email'         => ['required', 'string', 'max:50', 'email'],
            'employee_id'   => ['required', 'string', 'max:10'],
        ];
        if($request->filled('current_password') && $request->filled('new_password') && $request->filled('new_password_confirmation')) {
            $rules['current_password']          = ['required', 'current_password'];
            $rules['new_password']              = ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'];
            $rules['new_password_confirmation'] = 'required';
        } else {
            return redirect()->back()->with('toast-warning', 'Please fill in the current password and new password fields.');
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $user = User::findOrFail(Auth::id());
            $user->first_name   = $request->input('first_name');
            $user->middle_name  = $request->input('middle_name');
            $user->last_name    = $request->input('last_name');
            $user->email        = $request->input('email');
            $user->password     = Hash::make($request->input('new_password'));
            $user->save();
            $employeeDetails = EmployeeDetail::where('user_id', $user->id)->first();
            if ($employeeDetails) {
                $employeeDetails->employee_id = $request->input('employee_id');
                $employeeDetails->save();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Failed to update information. Please try again.');
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Information updated successfully!');
    }
    // public function edit(Request $request): View
    // {
    //     return view('profile.edit', [
    //         'user' => $request->user(),
    //     ]);
    // }

    // /**
    //  * Update the user's profile information.
    //  */
    // public function update(ProfileUpdateRequest $request): RedirectResponse
    // {
    //     $request->user()->fill($request->validated());

    //     if ($request->user()->isDirty('email')) {
    //         $request->user()->email_verified_at = null;
    //     }

    //     $request->user()->save();

    //     return Redirect::route('profile.edit')->with('status', 'profile-updated');
    // }

    // /**
    //  * Delete the user's account.
    //  */
    // public function destroy(Request $request): RedirectResponse
    // {
    //     $request->validateWithBag('userDeletion', [
    //         'password' => ['required', 'current_password'],
    //     ]);

    //     $user = $request->user();

    //     Auth::logout();

    //     $user->delete();

    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return Redirect::to('/');
    // }
}
