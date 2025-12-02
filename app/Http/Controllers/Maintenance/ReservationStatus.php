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
        return view('maintenance.status.index');
    }
    /**
     * Get the current status of the reservation system.
     */
    public function getReservationStatus()
    {
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
        $user = Auth::user();

        // 1. Authorization Check (Privilege ID 1 = Admin, 2 = Superadmin)
        if (!$user || ($user->privilege_id !== 1 && $user->privilege_id !== 2)) {
            Log::warning('Unauthorized reservation toggle attempt', ['user_id' => $user->id]);
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
        Log::info('Reservation system toggled', [
            'enabled' => $enabled,
            'admin_id' => $user->id,
            'admin_name' => $user->name ?? 'Unknown'
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
            // Fallback if table doesn't exist yet
            return response()->json([
                'pending' => 0,
                'available_for_pickup' => 0,
                'total_reserved' => 0
            ]);
        }
    }
}
