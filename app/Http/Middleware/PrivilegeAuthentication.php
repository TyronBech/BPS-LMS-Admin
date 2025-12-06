<?php

namespace App\Http\Middleware;

use App\Enum\PermissionsEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PrivilegeAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::guard('admin')->check()) return redirect()->route('login')->with('toast-error', 'You are not authenticated');
        $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
        if(!$authAdmin->hasPermissionTo(PermissionsEnum::ADD_PRIVILEGES) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::EDIT_PRIVILEGES) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::DELETE_PRIVILEGES)) return abort(403);
        return $next($request);
    }
}
