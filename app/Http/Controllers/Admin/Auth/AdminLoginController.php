<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminLoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function index(): View
    {
        Log::info('Admin Login: Login page accessed', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);

        return view('admin.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $email = trim($request->input('email'));
        
        $request->merge([
            'email' => $email,
        ]);

        Log::info('Admin Login: Authentication attempt initiated', [
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::error('Admin Login: Failed - User not found', [
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'Invalid email or password.')->withInput();
        }

        Log::debug('Admin Login: User found in database', [
            'user_id' => $user->id,
            'email' => $email,
            'user_name' => $user->full_name,
            'ip_address' => $request->ip(),
        ]);

        if ($user->getRoleNames()->isEmpty()) {
            Log::error('Admin Login: Failed - User has no admin role', [
                'user_id' => $user->id,
                'email' => $email,
                'user_name' => $user->full_name,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'You do not have admin access to this area.')->withInput();
        }

        Log::debug('Admin Login: User has valid admin role', [
            'user_id' => $user->id,
            'email' => $email,
            'roles' => $user->getRoleNames()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        if (Auth::guard('admin')->check()) {
            $currentUser = Auth::guard('admin')->user();

            Log::warning('Admin Login: Failed - Another user already logged in', [
                'attempted_email' => $email,
                'current_user_id' => $currentUser->id,
                'current_user_email' => $currentUser->email,
                'current_user_name' => $currentUser->full_name,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->intended(route('dashboard', absolute: false))->with('toast-error', 'A user is already logged in.');
        }

        Log::debug('Admin Login: Attempting authentication', [
            'email' => $email,
            'ip_address' => $request->ip(),
        ]);

        try {
            // Save into session + session table
            $request->authenticate();

            Log::info('Admin Login: Authentication successful', [
                'user_id' => $user->id,
                'email' => $email,
                'user_name' => $user->full_name,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            $oldSessionId = $request->session()->getId();
            $request->session()->regenerate();
            $newSessionId = $request->session()->getId();

            Log::debug('Admin Login: Session regenerated', [
                'user_id' => $user->id,
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'ip_address' => $request->ip(),
            ]);

            $request->session()->put('login_source', 'Admin');
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);

            // ensure session row is written to DB immediately
            $request->session()->save();

            // now update the custom column on the sessions table
            if (config('session.driver') === 'database') {
                $updated = DB::table(config('session.table', 'sessions'))
                    ->where('id', $request->session()->getId())
                    ->update(['login_source' => 'Admin']);

                Log::debug('Admin Login: Session updated in database', [
                    'user_id' => $user->id,
                    'session_id' => $request->session()->getId(),
                    'rows_updated' => $updated,
                    'ip_address' => $request->ip(),
                ]);
            }

            Log::info('Admin Login: Login completed successfully', [
                'user_id' => Auth::guard('admin')->user()->id,
                'user_name' => Auth::guard('admin')->user()->full_name,
                'email' => Auth::guard('admin')->user()->email,
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Admin Login: Authentication failed - Invalid credentials', [
                'email' => $email,
                'error_message' => $e->getMessage(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Admin Login: Unexpected error during authentication', [
                'email' => $email,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-error', 'An error occurred during login. Please try again.')->withInput();
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('admin')->user();
        $sessionId = $request->session()->getId();

        Log::info('Admin Logout: Logout process initiated', [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'user_email' => $user->email,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        try {
            Auth::guard('admin')->logout();

            Log::debug('Admin Logout: User logged out from guard', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            $request->session()->invalidate();

            Log::debug('Admin Logout: Session invalidated', [
                'user_id' => $user->id,
                'old_session_id' => $sessionId,
                'ip_address' => $request->ip(),
            ]);

            $request->session()->regenerateToken();

            Log::debug('Admin Logout: CSRF token regenerated', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            Log::info('Admin Logout: Logout completed successfully', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_email' => $user->email,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->route('main-welcome');

        } catch (\Exception $e) {
            Log::error('Admin Logout: Error during logout process', [
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->route('main-welcome')->with('toast-error', 'An error occurred during logout.');
        }
    }
}
