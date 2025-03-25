<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enum\PermissionsEnum; // Ensure PermissionsEnum is correctly imported

class UserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::guard('admin')->check()) return redirect()->route('dashboard')->with('toast-error', 'You are not authenticated');
        $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
        if(!$authAdmin->hasPermissionTo(PermissionsEnum::CREATE_USERS) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::EDIT_USERS) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::DELETE_USERS)) return redirect()->route('dashboard')->with('toast-error', 'You are unable to access this page');
        return $next($request);
    }
}
