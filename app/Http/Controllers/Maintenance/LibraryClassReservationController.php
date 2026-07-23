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

        $users = \App\Models\User::orderBy('first_name')->get();

        return view('maintenance.class-reservations.index', compact(
            'reservations',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'cancelledCount',
            'perPage',
            'activeTab',
            'calendarReservations',
            'users'
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

        $users = \App\Models\User::orderBy('first_name')->get();

        return view('maintenance.class-reservations.index', compact(
            'reservations',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'cancelledCount',
            'perPage',
            'activeTab',
            'calendarReservations',
            'users'
        ));
    }

    /**
     * Store a new class reservation directly added by the admin.
     */
    public function store(Request $request)
    {
        Log::info('Library Class Reservation: Admin creating reservation directly', [
            'admin_id' => Auth::guard('admin')->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validated = $request->validate([
            'user_id' => 'required|exists:usr_users,id',
            'faculty_user_id' => 'nullable|exists:usr_users,id',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);

            // Check if there is an already approved overlapping reservation
            $overlappingApproved = LibraryClassReservation::where('status', 'Approved')
                ->where('reservation_date', $validated['reservation_date'])
                ->where(function ($query) use ($validated) {
                    $endTime = $validated['end_time'] ?? $validated['start_time'];
                    $query->where('start_time', '<', $endTime)
                          ->where(DB::raw('COALESCE(end_time, start_time)'), '>', $validated['start_time']);
                })
                ->exists();

            if ($overlappingApproved) {
                DB::rollBack();
                return redirect()->back()->with('toast-warning', 'Cannot create reservation because an overlapping reservation already exists on this date and time.');
            }

            $admin = Auth::guard('admin')->user();
            $adminName = $admin->first_name . ' ' . $admin->last_name;

            $reservation = new LibraryClassReservation();
            $reservation->user_id = $validated['user_id'];
            $reservation->faculty_user_id = $validated['faculty_user_id'];
            $reservation->reservation_date = $validated['reservation_date'];
            $reservation->start_time = $validated['start_time'];
            $reservation->end_time = $validated['end_time'];
            $reservation->purpose = $validated['purpose'];
            $reservation->status = 'Approved';
            $reservation->approved_by = Auth::guard('admin')->id();
            $reservation->approved_at = now();
            $reservation->remarks = 'DIRECTLY ADDED and APPROVED by Admin: ' . $adminName . ' on ' . now()->format('F d, Y H:i:s');
            $reservation->save();

            // Automatically cancel other pending overlapping reservations in database
            $overlappingPending = LibraryClassReservation::where('status', 'Pending')
                ->where('id', '!=', $reservation->id)
                ->where('reservation_date', $reservation->reservation_date)
                ->where(function ($query) use ($reservation) {
                    $endTime = $reservation->end_time ?? $reservation->start_time;
                    $query->where('start_time', '<', $endTime)
                          ->where(DB::raw('COALESCE(end_time, start_time)'), '>', $reservation->start_time);
                })
                ->get();

            foreach ($overlappingPending as $other) {
                $other->status = 'Cancelled';
                $appendOtherRemarks = 'CANCELLED automatically due to overlapping approved reservation #' . $reservation->id . ' on ' . now()->format('F d, Y H:i:s');
                $other->remarks = $other->remarks ? ($other->remarks . ' || ' . $appendOtherRemarks) : $appendOtherRemarks;
                $other->save();
            }

            DB::commit();

            // Notify each of the cancelled reservation requesters
            foreach ($overlappingPending as $other) {
                $this->sendReservationEmail($other->user, $other, 'Cancelled', 'Cancelled due to overlapping reservation approval.');
            }

            Log::info('Library Class Reservation: Admin created and approved successfully', [
                'admin_id' => Auth::guard('admin')->id(),
                'reservation_id' => $reservation->id,
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-success', 'Library class reservation has been successfully added and approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Library Class Reservation: Failed to create request', [
                'admin_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to add reservation. Please try again.');
        }
    }

    /**
     * Check for overlapping approved reservations dynamically via AJAX.
     */
    public function checkConflict(Request $request)
    {
        $date = $request->query('date');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');

        if (!$date || !$startTime || !$endTime) {
            return response()->json(['conflict' => false]);
        }

        $conflict = LibraryClassReservation::where('status', 'Approved')
            ->where('reservation_date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where(DB::raw('COALESCE(end_time, start_time)'), '>', $startTime);
            })
            ->exists();

        return response()->json(['conflict' => $conflict]);
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

            // 1. Check if there is an already approved overlapping reservation
            $overlappingApproved = LibraryClassReservation::where('status', 'Approved')
                ->where('reservation_date', $reservation->reservation_date)
                ->where(function ($query) use ($reservation) {
                    $endTime = $reservation->end_time ?? $reservation->start_time;
                    $query->where('start_time', '<', $endTime)
                          ->where(DB::raw('COALESCE(end_time, start_time)'), '>', $reservation->start_time);
                })
                ->exists();

            if ($overlappingApproved) {
                DB::rollBack();
                Log::warning('Library Class Reservation Approval: Conflict detected', [
                    'user_id' => Auth::guard('admin')->id(),
                    'reservation_id' => $id,
                    'timestamp' => now(),
                ]);
                return redirect()->back()->with('toast-warning', 'Cannot approve this reservation because an overlapping reservation has already been approved.');
            }
            
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

            // 2. Automatically cancel other pending overlapping reservations in database
            $overlappingPending = LibraryClassReservation::where('status', 'Pending')
                ->where('id', '!=', $reservation->id)
                ->where('reservation_date', $reservation->reservation_date)
                ->where(function ($query) use ($reservation) {
                    $endTime = $reservation->end_time ?? $reservation->start_time;
                    $query->where('start_time', '<', $endTime)
                          ->where(DB::raw('COALESCE(end_time, start_time)'), '>', $reservation->start_time);
                })
                ->get();

            foreach ($overlappingPending as $other) {
                $other->status = 'Cancelled';
                $appendOtherRemarks = 'CANCELLED automatically due to overlapping approved reservation #' . $reservation->id . ' on ' . now()->format('F d, Y H:i:s');
                $other->remarks = $other->remarks ? ($other->remarks . ' || ' . $appendOtherRemarks) : $appendOtherRemarks;
                $other->save();
            }
            
            DB::commit();

            // Notify the requester
            $this->sendReservationEmail($reservation->user, $reservation, 'Approved', $remarks);

            // Notify each of the cancelled reservation requesters
            foreach ($overlappingPending as $other) {
                $this->sendReservationEmail($other->user, $other, 'Cancelled', 'Cancelled due to overlapping reservation approval.');
            }

            Log::info('Library Class Reservation Approval: Approved successfully and cancelled conflicts', [
                'user_id' => Auth::guard('admin')->id(),
                'reservation_id' => $id,
                'cancelled_ids' => $overlappingPending->pluck('id')->toArray(),
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
        session()->save(); // Release session lock early for concurrent polling
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
