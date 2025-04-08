<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enum\PermissionsEnum;

class ReportAuthentication
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
        if(!$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_USER_REPORTS)
            && !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_INVENTORY_REPORTS)
            && !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_SUMMARY_REPORTS)
            && !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_TRANSACTION_REPORTS)
            && !$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_BOOK_CIRCULATION_REPORTS)) return redirect()->route('dashboard')->with('toast-error', 'You are unable to access this page');
        return $next($request);
    }
}
