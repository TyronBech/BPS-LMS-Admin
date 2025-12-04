<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationStatus extends Controller
{
    public function index()
    {
        Log::info('Reservation Status: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);
        return view('maintenance.status.index');
    }
    /**
     * Get the current status of the reservation system.
     */
    public function getReservationStatus()
    {
        Log::debug('Reservation Status: Fetching current status', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        $status = SystemSetting::where('key', 'reservation_system_active')
            ->value('value');

        // Interpret 'true' string or '1' as active
        $isActive = $status === 'true' || $status === '1';

        return response()->json([
            'status' => $isActive,
            'message' => $isActive ? 'Reservation system is ACTIVE' : 'Reservation system is INACTIVE',
            'raw_value' => $status
        ]);
    }

    /**
     * Toggle the reservation system ON or OFF.
     */
    public function toggleReservationSystem(Request $request)
    {
        $user = Auth::guard('admin')->user();

        // 1. Authorization Check (Privilege ID 1 = Admin, 2 = Superadmin)
        if (!$user || ($user->privilege_id !== 1 && $user->privilege_id !== 2)) {
            Log::warning('Reservation Status: Unauthorized toggle attempt', [
                'user_id' => $user ? $user->id : 'guest',
                'user_name' => $user ? ($user->full_name ?? $user->first_name) : 'guest',
                'privilege_id' => $user ? $user->privilege_id : null,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        // 2. Validate Input
        $enabled = $request->input('enabled', false);
        $valueStr = $enabled ? 'true' : 'false';

        // 3. Update Database
        SystemSetting::updateOrInsert(
            ['key' => 'reservation_system_active'],
            ['value' => $valueStr, 'updated_at' => now()]
        );

        // 4. Log the Action
        Log::info('Reservation Status: System toggled', [
            'user_id' => $user->id,
            'user_name' => $user->full_name ?? $user->first_name,
            'action' => $enabled ? 'ACTIVATED' : 'DEACTIVATED',
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $enabled ? 'Reservation system ACTIVATED' : 'Reservation system DEACTIVATED',
            'enabled' => $enabled,
            'stored_value' => $valueStr
        ]);
    }

    /**
     * Get basic queue stats for the dashboard.
     */
    public function getReservationStats()
    {
        Log::debug('Reservation Status: Fetching statistics', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        // NOTE: Adjust table/column names based on your actual Books/Reservations schema
        // This is a placeholder logic based on the guide.
        try {
            // Example logic (Modify 'reservations' to your actual table name)
            $pending = Reservation::where('status', 'pending')->count();
            $pickup = Reservation::where('status', 'available_pickup')->count();

            return response()->json([
                'pending' => $pending,
                'available_for_pickup' => $pickup,
                'total_reserved' => $pending + $pickup,
                'last_updated' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            Log::error('Reservation Status: Error fetching statistics', [
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            // Fallback if table doesn't exist yet
            return response()->json([
                'pending' => 0,
                'available_for_pickup' => 0,
                'total_reserved' => 0
            ]);
        }
    }
}
