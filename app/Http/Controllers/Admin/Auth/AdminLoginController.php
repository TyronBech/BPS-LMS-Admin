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
use App\Mail\TwoFactorMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminLoginController extends Controller
{
    /**
     * Display the login view.
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
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
     * 
     * @param  \App\Http\Requests\Auth\AdminLoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
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

        // Check rate limiting first
        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($email) . '|' . $request->ip());
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            
            Log::warning('Admin Login: Rate limited', [
                'email' => $email,
                'ip_address' => $request->ip(),
                'lockout_seconds' => $seconds,
                'timestamp' => now(),
            ]);
            
            return redirect()->back()
                ->with('lockout_time', $seconds)
                ->with('toast-error', trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]))
                ->withInput();
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::error('Admin Login: Failed - User not found', [
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            $this->handleFailedAttempt($throttleKey);
            return $this->handleLockoutResponse($request, $throttleKey, 'Invalid email or password.');
        }
        
        Log::debug('Admin Login: User found in database', [
            'user_id' => $user->id,
            'email' => $email,
            'user_name' => $user->full_name,
            'ip_address' => $request->ip(),
        ]);

        if(!Hash::check($request->password, $user->password)) {
            Log::error('Admin Login: Failed - Invalid password', [
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
            
            $this->handleFailedAttempt($throttleKey);
            return $this->handleLockoutResponse($request, $throttleKey, 'Invalid email or password.');
        }

        Log::debug('Admin Login: Password matched', [
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

            return redirect()->route('dashboard')->with('toast-error', 'A user is already logged in.');
        }

        Log::debug('Admin Login: Attempting authentication', [
            'email' => $email,
            'ip_address' => $request->ip(),
        ]);

        try {
            // Check if user has 2FA enabled before authenticating
            if ($user->two_factor_enabled) {
                // Check if coming from 2FA verification
                if (!$request->session()->get('2fa_passed')) {
                    Log::info('Admin Login: 2FA required for user', [
                        'user_id' => $user->id,
                        'email' => $email,
                        'ip_address' => $request->ip(),
                        'timestamp' => now(),
                    ]);

                    // Store credentials temporarily
                    $request->session()->put('2fa_user_id', $user->id);
                    $request->session()->put('2fa_email', $email);
                    $request->session()->put('2fa_password', (string)$request->password); // keep raw
                    $request->session()->put('2fa_remember', (bool)$request->remember);
                    $request->session()->put('show_2fa_modal', true);

                    // Generate and send OTP
                    $otpSent = $this->generateAndSendOTP($email);

                    if (!$otpSent) {
                        Log::error('Admin Login: Failed to send 2FA OTP', [
                            'user_id' => $user->id,
                            'email' => $email,
                            'timestamp' => now(),
                        ]);
                        return redirect()->back()->with('toast-error', 'Failed to send verification code. Please try again.')->withInput();
                    }

                    return redirect()->back()->with('show_2fa_modal', true);
                }

                // Clear 2FA session flag after successful verification
                $request->session()->forget('2fa_passed');
            }

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

            // Clear 2FA temporary data
            $request->session()->forget(['2fa_user_id', '2fa_email', '2fa_password', '2fa_remember']);

            Log::info('Admin Login: Login completed successfully', [
                'user_id' => Auth::guard('admin')->user()->id,
                'user_name' => Auth::guard('admin')->user()->full_name,
                'email' => Auth::guard('admin')->user()->email,
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);

            return redirect()->route('dashboard');

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
     * Handle a failed login attempt by incrementing the rate limiter.
     *
     * @param  string  $throttleKey
     * @return void
     */
    private function handleFailedAttempt(string $throttleKey): void
    {
        $penaltyKey = $throttleKey . '.penalty';
        $lockoutLevel = \Illuminate\Support\Facades\Cache::get($penaltyKey, 0);
        
        // Decay duration tiers: 1 min, 5 mins, 15 mins, 1 hour
        $decaySeconds = match($lockoutLevel) {
            0 => 60,
            1 => 300,
            2 => 900,
            default => 3600,
        };

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, $decaySeconds);

        // If this failure caused a lockout, increment the penalty level for next time
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            \Illuminate\Support\Facades\Cache::put($penaltyKey, $lockoutLevel + 1, now()->addDay());
        }
    }

    /**
     * Handle the lockout response after a failed login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $throttleKey
     * @param  string  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleLockoutResponse($request, string $throttleKey, string $message): RedirectResponse
    {
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            
            return redirect()->back()
                ->with('lockout_time', $seconds)
                ->with('toast-error', trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]))
                ->withInput();
        }
        
        return redirect()->back()->with('toast-error', $message)->withInput();
    }

    /**
     * Show 2FA verification page.
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show2FA()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        Log::info('Admin Login: 2FA verification page accessed', [
            'user_id' => session('2fa_user_id'),
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return view('admin.auth.two-factor');
    }

    /**
     * Verify 2FA code and complete login.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify2FA(Request $request): RedirectResponse
    {
        // normalize input
        $request->merge(['code' => preg_replace('/\D/', '', trim((string)$request->input('code')))]);
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId   = $request->session()->get('2fa_user_id');
        $email    = (string) $request->session()->get('2fa_email');
        $password = (string) $request->session()->get('2fa_password'); // raw password from first form
        $remember = (bool) $request->session()->get('2fa_remember');

        if (!$userId || $email === '' || $password === '') {
            return redirect()->route('login')->with('toast-error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);
        if (!$user || strcasecmp($user->email, $email) !== 0) {
            return redirect()->route('login')->with('toast-error', 'User not found. Please login again.');
        }

        // Verify OTP
        if (!$this->verifyOTP($user, $request->code)) {
            return redirect()->back()->with('toast-error', 'Invalid verification code. Please try again.');
        }

        // Re-check original password against stored hash to prevent bypass
        if (!Hash::check($password, $user->password)) {
            // Clear 2FA state so user can retry cleanly
            $request->session()->forget(['2fa_user_id','2fa_email','2fa_password','2fa_remember','show_2fa_modal']);
            return redirect()->route('login')->with('toast-error', 'These credentials do not match our records.');
        }

        // Login directly with the verified user
        Auth::guard('admin')->login($user, $remember);

        // Regenerate session
        $oldSessionId = $request->session()->getId();
        $request->session()->regenerate();
        $newSessionId = $request->session()->getId();

        // Mark source and persist
        $request->session()->put('login_source', 'Admin');
        DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
        $request->session()->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('id', $request->session()->getId())
                ->update(['login_source' => 'Admin']);
        }

        // Clear temporary 2FA data
        $request->session()->forget(['2fa_user_id','2fa_email','2fa_password','2fa_remember','show_2fa_modal']);

        return redirect()->route('dashboard');
    }

    /**
     * Resend 2FA code.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend2FA(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');
        $email = $request->session()->get('2fa_email');

        if (!$userId || !$email) {
            return redirect()->route('login')->with('toast-error', 'Session expired. Please login again.');
        }

        Log::info('Admin Login: 2FA code resend requested', [
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $otpSent = $this->generateAndSendOTP($email);

        if (!$otpSent) {
            Log::error('Admin Login: Failed to resend 2FA OTP', [
                'user_id' => $userId,
                'email' => $email,
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to send verification code. Please try again.');
        }

        Log::info('Admin Login: 2FA code resent successfully', [
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        return redirect()->back()->with('toast-success', 'Verification code has been resent to your email.');
    }
    /**
     * Generate and send OTP to user's email for 2FA.
     *
     * @param  string  $email
     * @return bool
     */
    private function generateAndSendOTP($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user || !$user->two_factor_enabled) {
            return false;
        }

        // Generate 6-digit OTP (leading zeros preserved)
        $otp = sprintf('%06d', random_int(0, 999999));

        DB::beginTransaction();
        try {
            // Always overwrite any previous OTP
            $expiresAt = now()->addMinutes(10)->timestamp;
            $user->two_factor_secret = Hash::make($otp) . '|' . $expiresAt;
            $user->save();
            DB::commit();

            Log::info('2FA: OTP generated', [
                'user_id'    => $user->id,
                'email'      => $user->email,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            ]);

            $this->sendOTPMail($user, $otp);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('2FA: OTP generation failed', [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify OTP for 2FA.
     *
     * @param  \App\Models\User  $user
     * @param  string  $otp
     * @return bool
     */
    private function verifyOTP($user, $otp)
    {
        // Normalize input: trim and digits only
        $otp = preg_replace('/\D/', '', trim((string)$otp));

        if (strlen($otp) !== 6) {
            Log::warning('2FA: OTP format invalid', [
                'user_id' => $user->id,
                'length'  => strlen($otp),
            ]);
            return false;
        }

        if (!$user->two_factor_enabled || empty($user->two_factor_secret)) {
            Log::warning('2FA: Not enabled or missing secret', [
                'user_id' => $user->id,
            ]);
            return false;
        }

        // Support both "hash|timestamp" and legacy "hash" formats
        $parts = explode('|', $user->two_factor_secret, 2);
        $hashedOTP = $parts[0] ?? '';
        $expiryTimestamp = isset($parts[1]) ? (int)$parts[1] : null;

        // Expiry check if timestamp exists
        if ($expiryTimestamp !== null && now()->timestamp > $expiryTimestamp) {
            Log::warning('2FA: OTP expired', [
                'user_id'     => $user->id,
                'expired_at'  => date('Y-m-d H:i:s', $expiryTimestamp),
                'current_now' => now()->toDateTimeString(),
            ]);
            DB::beginTransaction();
            try {
                $user->two_factor_secret = null;
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
            return false;
        }

        // Verify OTP
        if (!empty($hashedOTP) && Hash::check($otp, $hashedOTP)) {
            DB::beginTransaction();
            try {
                // Clear after success
                $user->two_factor_secret = null;
                $user->save();
                DB::commit();

                Log::info('2FA: OTP verified', [
                    'user_id' => $user->id,
                ]);
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('2FA: DB error clearing OTP after verify', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
                return false;
            }
        }

        // Backup code check
        if (!empty($user->two_factor_backup_codes)) {
            $backupCodes = json_decode($user->two_factor_backup_codes, true);
            if (is_array($backupCodes) && in_array(strtoupper($otp), $backupCodes, true)) {
                DB::beginTransaction();
                try {
                    // Remove used backup code and clear pending OTP
                    $backupCodes = array_values(array_diff($backupCodes, [strtoupper($otp)]));
                    $user->two_factor_backup_codes = json_encode($backupCodes);
                    $user->two_factor_secret = null;
                    $user->save();
                    DB::commit();

                    Log::info('2FA: Backup code verified', [
                        'user_id'          => $user->id,
                        'remaining_codes'  => count($backupCodes),
                    ]);
                    return true;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('2FA: DB error updating backup codes', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                    return false;
                }
            }
        }

        Log::warning('2FA: Invalid OTP or backup code', [
            'user_id' => $user->id,
        ]);
        return false;
    }

    /**
     * Sends an email with the OTP code.
     *
     * @param  \App\Models\User  $user
     * @param  string  $otp
     */
    private function sendOTPMail($user, $otp)
    {
        Log::info('Profile: Sending OTP email', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now(),
        ]);
        
        try {
            Mail::to($user->email)->send(new TwoFactorMail($user, $otp));
            
            Log::info('Profile: OTP email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Profile: Failed to send OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
        }
    }
    /**
     * Cancel 2FA and clear session.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel2FA(Request $request): RedirectResponse
    {
        Log::info('Admin Login: 2FA cancelled by user', [
            'user_id' => $request->session()->get('2fa_user_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        // Clear 2FA session data
        $request->session()->forget(['2fa_user_id', '2fa_email', '2fa_password', '2fa_remember', 'show_2fa_modal']);

        return redirect()->route('login')->with('toast-info', 'Login cancelled.');
    }
    /**
     * Destroy an authenticated session.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
