<?php

namespace App\Http\Middleware;

use App\Enum\PermissionsEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if(!Auth::guard('admin')->check()) return redirect()->route('dashboard')->with('toast-error', 'You are not authenticated');
        // $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
        // if(!$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_USERS_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_BOOKS_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_BOOK_CATEGORIES_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_PRIVILEGES_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_PENALTY_RULES_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_TRANSACTIONS_MAINTENANCE) &&
        //     !$authAdmin->hasPermissionTo(PermissionsEnum::MODIFY_ADMIN)) return redirect()->route('dashboard')->with('toast-error', 'You are unable to access this page');
        return $next($request);
    }
}
