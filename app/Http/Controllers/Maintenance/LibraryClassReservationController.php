<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\LibraryClassReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClassReservationMail;

class LibraryClassReservationController extends Controller
{
    /**
     * Show all library class reservations.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $activeTab = $request->input('tab', 'Pending');
        
        if (!in_array($activeTab, ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Calendar'])) {
            $activeTab = 'Pending';
        }

        Log::info('Library Class Reservations: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->first_name . ' ' . Auth::guard('admin')->user()->last_name,
            'per_page' => $perPage,
            'tab' => $activeTab,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'sometimes|integer|min:1|max:500',
            'tab' => 'sometimes|string|in:Pending,Approved,Rejected,Cancelled,Calendar',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.class-reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $calendarReservations = collect();

        if ($activeTab === 'Calendar') {
            $calendarReservations = LibraryClassReservation::whereIn('status', ['Approved', 'Pending'])
                ->with(['user', 'faculty'])
                ->orderBy('reservation_date')
                ->orderBy('start_time')
                ->get();

            $reservations = LibraryClassReservation::where('id', 0)
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'tab' => $activeTab
                ]);
        } else {
            // Query class reservations
            $query = LibraryClassReservation::where('status', $activeTab);

            $reservations = $query->with(['user', 'faculty', 'approver'])
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'tab' => $activeTab
                ]);
        }

        // Get counts for statistics
        $pendingCount = LibraryClassReservation::where('status', 'Pending')->count();
        $approvedCount = LibraryClassReservation::where('status', 'Approved')->count();
        $rejectedCount = LibraryClassReservation::where('status', 'Rejected')->count();
        $cancelledCount = LibraryClassReservation::where('status', 'Cancelled')->count();

        return view('maintenance.class-reservations.index', compact(
            'reservations',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'cancelledCount',
            'perPage',
            'activeTab',
            'calendarReservations'
        ));
    }

    /**
     * Search class reservations.
     */
    public function search(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $activeTab = $request->input('tab', 'Pending');
        if (!in_array($activeTab, ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Calendar'])) {
            $activeTab = 'Pending';
        }

        Log::info('Library Class Reservations: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'search_term' => $request->search,
            'tab' => $activeTab,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
            'perPage' => 'sometimes|integer|min:1|max:500',
            'tab' => 'sometimes|string|in:Pending,Approved,Rejected,Cancelled,Calendar',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.class-reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $query = LibraryClassReservation::where('status', $activeTab);

        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($sq) use ($searchTerm) {
                    $sq->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                })->orWhereHas('faculty', function ($sq) use ($searchTerm) {
                    $sq->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm);
                })->orWhere('purpose', 'like', $searchTerm)
                  ->orWhere('reservation_date', 'like', $searchTerm);
            });
        }

        $reservations = $query->with(['user', 'faculty', 'approver'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'tab' => $activeTab,
                'search' => $request->search
            ]);

        // Statistics
        $pendingCount = LibraryClassReservation::where('status', 'Pending')->count();
        $approvedCount = LibraryClassReservation::where('status', 'Approved')->count();
        $rejectedCount = LibraryClassReservation::where('status', 'Rejected')->count();
        $cancelledCount = LibraryClassReservation::where('status', 'Cancelled')->count();

        $calendarReservations = collect();
        if ($activeTab === 'Calendar') {
            $calendarReservations = LibraryClassReservation::whereIn('status', ['Approved', 'Pending'])
                ->with(['user', 'faculty'])
                ->orderBy('reservation_date')
                ->orderBy('start_time')
                ->get();
        }

        return view('maintenance.class-reservations.index', compact(
            'reservations',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'cancelledCount',
            'perPage',
            'activeTab',
            'calendarReservations'
        ));
    }

    /**
     * Approve reservation request.
     */
    public function approve(Request $request, $id)
    {
        Log::info('Library Class Reservation Approval: Attempting to approve request', [
            'user_id' => Auth::guard('admin')->id(),
            'reservation_id' => $id,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $reservation = LibraryClassReservation::where('status', 'Pending')->findOrFail($id);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            
            $remarks = $request->input('remarks');
            $admin = Auth::guard('admin')->user();
            $adminName = $admin->first_name . ' ' . $admin->last_name;
            
            $reservation->status = 'Approved';
            $reservation->approved_by = Auth::guard('admin')->id();
            $reservation->approved_at = now();
            
            $appendRemarks = 'APPROVED by ' . $adminName . ' on ' . now()->format('F d, Y H:i:s');
            if ($remarks) {
                $appendRemarks .= ' | Remarks: ' . $remarks;
            }
            
            $reservation->remarks = $reservation->remarks ? ($reservation->remarks . ' || ' . $appendRemarks) : $appendRemarks;
            $reservation->save();
            
            DB::commit();

            // Notify the requester
            $this->sendReservationEmail($reservation->user, $reservation, 'Approved', $remarks);

            Log::info('Library Class Reservation Approval: Approved successfully', [
                'user_id' => Auth::guard('admin')->id(),
                'reservation_id' => $id,
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-success', 'Library class reservation has been approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Library Class Reservation Approval: Failed to approve request', [
                'user_id' => Auth::guard('admin')->id(),
                'reservation_id' => $id,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * Reject reservation request.
     */
    public function reject(Request $request, $id)
    {
        Log::info('Library Class Reservation Rejection: Attempting to reject request', [
            'user_id' => Auth::guard('admin')->id(),
            'reservation_id' => $id,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $reservation = LibraryClassReservation::where('status', 'Pending')->findOrFail($id);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            
            $admin = Auth::guard('admin')->user();
            $adminName = $admin->first_name . ' ' . $admin->last_name;
            
            $reservation->status = 'Rejected';
            $reservation->rejected_at = now();
            
            $appendRemarks = 'REJECTED by ' . $adminName . ' on ' . now()->format('F d, Y H:i:s') . ' | Reason: ' . $validated['rejection_reason'];
            $reservation->remarks = $reservation->remarks ? ($reservation->remarks . ' || ' . $appendRemarks) : $appendRemarks;
            $reservation->save();
            
            DB::commit();

            // Notify the requester
            $this->sendReservationEmail($reservation->user, $reservation, 'Rejected', $validated['rejection_reason']);

            Log::info('Library Class Reservation Rejection: Rejected successfully', [
                'user_id' => Auth::guard('admin')->id(),
                'reservation_id' => $id,
                'reason' => $validated['rejection_reason'],
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-success', 'Library class reservation has been rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Library Class Reservation Rejection: Failed to reject request', [
                'user_id' => Auth::guard('admin')->id(),
                'reservation_id' => $id,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to reject request. Please try again.');
        }
    }

    /**
     * Return pending class reservations count.
     */
    public function pendingCount()
    {
        $count = LibraryClassReservation::where('status', 'Pending')->count();
        return response()->json(['pending_count' => $count]);
    }

    /**
     * Send email notification for class room reservation status.
     */
    private function sendReservationEmail($user, $reservation, $status, $remarks = null)
    {
        if ($user && $user->email) {
            try {
                $statusMessage = $status === 'Approved'
                    ? 'Your class room reservation request has been approved. Please find the reservation details below.'
                    : 'Your class room reservation request has been rejected. Reason: ' . ($remarks ?? 'No reason provided') . '.';

                Mail::to($user->email)->send(new ClassReservationMail(
                    $user,
                    $reservation,
                    $statusMessage,
                    $status,
                    $remarks
                ));

                Log::info('Library Class Reservation: Status email sent', [
                    'recipient_email' => $user->email,
                    'reservation_id' => $reservation->id,
                    'status' => $status,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Library Class Reservation: Failed to send status email', [
                    'recipient_email' => $user->email,
                    'reservation_id' => $reservation->id,
                    'status' => $status,
                    'error_message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);
            }
        }
    }
}
