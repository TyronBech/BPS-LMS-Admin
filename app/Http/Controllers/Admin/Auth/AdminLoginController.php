<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\AdminLoginRequest;

class AdminLoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function index(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        foreach ($request->all() as $input) {
            if ($this->hasSqlInjection($input)) {
                abort(400, 'Suspicious input detected.');
            }
        }
        $request->authenticate();

        $request->session()->regenerate();
        
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        //dd(Auth::guard('admin')->user());
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('main-welcome');
    }
    /** * Check for SQL injection patterns in the input.
     * @param mixed $input
     * @return bool
     */
    private function hasSqlInjection($input): bool
    {
        $upper = is_string($input) ? strtoupper($input) : '';
        $pattern = '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|RANDOMBLOB|CASE\s+|--|#)\b|["\'=;])/i';
        return is_string($input) && preg_match($pattern, $upper);
    }
}
