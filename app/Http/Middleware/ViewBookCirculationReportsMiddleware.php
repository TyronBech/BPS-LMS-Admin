<?php

namespace App\Http\Middleware;

use App\Enum\PermissionsEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ViewBookCirculationReportsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) return redirect()->route('login')->with('toast-error', 'You are not authenticated');

        $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);

        if (!$authAdmin->hasPermissionTo(PermissionsEnum::VIEW_BOOK_CIRCULATION_REPORTS)) return abort(403);

        return $next($request);
    }
}
