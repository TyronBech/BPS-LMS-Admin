<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enum\PermissionsEnum;

class PenaltyRuleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user has permission to manage penalty rules
        if(!Auth::guard('admin')->check()) return redirect()->route('dashboard')->with('toast-error', 'You are not authenticated');
        $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
        if(!$authAdmin->hasPermissionTo(PermissionsEnum::ADD_PENALTY_RULES) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::EDIT_PENALTY_RULES) && 
            !$authAdmin->hasPermissionTo(PermissionsEnum::DELETE_PENALTY_RULES)) return redirect()->route('dashboard')->with('toast-error', 'You are unable to access this page');
        return $next($request);
    }
}
