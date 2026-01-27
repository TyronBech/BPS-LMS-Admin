<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangePasswordMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index()
    {
        Log::info('Profile: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $user = User::findOrFail(Auth::id());
        return view('profile.index', compact('user'));
    }
    /**
     * Update the user's information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        Log::info('Profile: Update attempt', [
            'user_id' => Auth::guard('admin')->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $rules = [
            'first_name'    => ['required', 'string', 'max:50'],
            'middle_name'   => ['nullable', 'string', 'max:50'],
            'last_name'     => ['required', 'string', 'max:50'],
            'suffix'        => ['nullable', 'string', 'max:10'],
            'email'         => ['required', 'string', 'max:50', 'email'],
            'user_id'       => ['required', 'string', 'max:50'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];
        if ($request->filled('current_password')) {
            if ($request->filled('new_password') && $request->filled('new_password_confirmation')) {
                $rules['current_password']          = ['required', 'current_password'];
                $rules['new_password']              = ['required', 'string', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised(), 'confirmed'];
                $rules['new_password_confirmation'] = 'required';
            } else {
                Log::warning('Profile: Update failed - Missing password confirmation', [
                    'user_id' => Auth::guard('admin')->id(),
                    'timestamp' => now(),
                ]);
                return redirect()->back()->with('toast-warning', 'Please fill in the new password and confirmation fields.')->withInput();
            }
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            Log::error('Profile: Validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $user = User::findOrFail(Auth::id());
            $user->first_name   = $request->input('first_name');
            $user->middle_name  = $request->input('middle_name');
            $user->last_name    = $request->input('last_name');
            $user->suffix       = $request->input('suffix');
            $user->email        = $request->input('email');

            if ($request->hasFile('profile_image')) {
                $base64Image = base64_encode(file_get_contents($request->file('profile_image')->getRealPath()));
                $user->profile_image = $base64Image;
            }

            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->input('new_password'));
            }

            $user->save();
            if ($user->privileges->user_type === 'student') {
                $user->students->id_number = $request->input('user_id');
                $user->students->save();
            } elseif ($user->privileges->user_type === 'employee') {
                $user->employees->employee_id = $request->input('user_id');
                $user->employees->save();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Profile: Update failed - Database error', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to update information. Please try again.')->withInput();
        }
        DB::commit();

        Log::info('Profile: Information updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        // Send email notification
        if ($request->filled('new_password')) {
            $this->changePasswordMail($user);
        }
        return redirect()->back()->with('toast-success', 'Information updated successfully!');
    }
    /**
     * Enable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableTwoFactor(Request $request)
    {
        Log::info('Profile: 2FA Enable attempt', [
            'user_id' => Auth::guard('admin')->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::guard('admin')->user()->password)) {
            Log::warning('Profile: 2FA Enable failed - Incorrect password', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return back()->with('toast-error', 'Incorrect password.');
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            
            $user = User::findOrFail(Auth::guard('admin')->id());
            $user->two_factor_enabled = 1;
            
            // Generate backup codes (10 codes)
            $backupCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $backupCodes[] = strtoupper(Str::random(8));
            }
            $user->two_factor_backup_codes = json_encode($backupCodes);
            
            $user->save();
            
            DB::commit();

            Log::info('Profile: 2FA enabled successfully', [
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);

            return back()->with([
                'toast-success' => 'Two-factor authentication has been enabled successfully.',
                'backup_codes' => $backupCodes
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile: 2FA Enable failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return back()->with('toast-error', 'Failed to enable two-factor authentication. Please try again.');
        }
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTwoFactor(Request $request)
    {
        Log::info('Profile: 2FA Disable attempt', [
            'user_id' => Auth::guard('admin')->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::guard('admin')->user()->password)) {
            Log::warning('Profile: 2FA Disable failed - Incorrect password', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return back()->with('toast-error', 'Incorrect password.');
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            
            $user = User::findOrFail(Auth::guard('admin')->id());
            $user->two_factor_enabled = 0;
            $user->two_factor_secret = null;
            $user->two_factor_backup_codes = null;
            $user->save();
            
            DB::commit();

            Log::info('Profile: 2FA disabled successfully', [
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);

            return back()->with('toast-success', 'Two-factor authentication has been disabled.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile: 2FA Disable failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return back()->with('toast-error', 'Failed to disable two-factor authentication. Please try again.');
        }
    }

    /**
     * Sends an email notification to the user when their password is updated.
     *
     * @param  \App\Models\User  $user
     */
    private function changePasswordMail($user)
    {
        Log::info('Profile: Sending password change email', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now(),
        ]);
        try {
            Mail::to($user->email)->send(new ChangePasswordMail($user));
        } catch (\Exception $e) {
            Log::error('Profile: Failed to send password change email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
        }
        Log::info('Profile: Password change email sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now(),
        ]);
    }
}
