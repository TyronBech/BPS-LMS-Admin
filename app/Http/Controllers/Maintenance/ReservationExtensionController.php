<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Mail\ReservationMail;
use App\Models\StudentDetail;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ReservationExtensionController extends Controller
{
    /**
     * Show all pending reservation or extension requests
     */
    public function index(Request $request)
    {   
        $perPage = $request->input('perPage', 10);
        $activeTab = $request->input('tab', 'reservations');
        if (!in_array($activeTab, ['reservations', 'extensions'])) {
            $activeTab = 'reservations';
        }

        Log::info('Reservation/Extension Approvals: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'per_page' => $perPage,
            'tab' => $activeTab,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'sometimes|integer|min:1|max:500',
            'tab' => 'sometimes|string|in:reservations,extensions',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        // Base query for the list
        $query = Transaction::where('status', 'Pending');
        if ($activeTab === 'reservations') {
            $query->where('transaction_type', 'Reserved');
        } else {
            $query->where('transaction_type', 'Borrowed');
        }

        $pendingRequests = $query->with(['user', 'book'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'tab' => $activeTab
            ]);

        // Calculate statistics counts
        $pendingReservationsCount = Transaction::where('transaction_type', 'Reserved')
            ->where('status', 'Pending')
            ->count();

        $pendingExtensionsCount = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();

        $pendingExtensionCount = $pendingReservationsCount + $pendingExtensionsCount;
        
        // Count approved extensions this month
        $approvedCount = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->whereIn('status', ['Borrowed', 'Available for pick up'])
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        
        // Count active borrowings
        $activeBorrowings = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->whereIn('status', ['Borrowed', 'Available for pick up'])
            ->count();

        Log::debug('Reservation/Extension Approvals: Statistics retrieved', [
            'pending_reservations' => $pendingReservationsCount,
            'pending_extensions' => $pendingExtensionsCount,
            'approved_month_count' => $approvedCount,
            'active_borrowings' => $activeBorrowings,
            'timestamp' => now(),
        ]);

        return view('maintenance.reservations.index', compact(
            'pendingRequests', 
            'pendingReservationsCount',
            'pendingExtensionsCount',
            'pendingExtensionCount', 
            'approvedCount',
            'activeBorrowings',
            'perPage',
            'activeTab'
        ));
    }

    public function pendingExtensionCount()
    {
        $count = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Pending')
            ->count();

        return response()->json(['pending_extension_count' => $count]);
    }

    /**
     * Approve reservation or extension request
     */
    public function approve($id)
    {
        Log::info('Reservation/Extension Approval: Attempting to approve request', [
            'user_id' => Auth::guard('admin')->id(),
            'transaction_id' => $id,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $transaction = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Pending')
            ->findOrFail($id);

        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $user = $transaction->user;
            $book = $transaction->book;
            
            if ($transaction->transaction_type === 'Reserved') {
                // Book Reservation Approval
                if ($book && $book->availability_status === 'Available') {
                    // Book is available in library
                    $transaction->status = 'Available for pick up';
                    $transaction->reserved_date = now()->format('Y-m-d');
                    $newDeadline = now()->addDays(3); // 3 days pickup deadline
                    $transaction->pickup_deadline = $newDeadline->format('Y-m-d');
                    $transaction->remarks = $transaction->remarks . ' | RESERVATION APPROVED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s') . ' (Available for Pickup)';
                    $transaction->save();

                    $book->update(['availability_status' => 'Reserved']);

                    $this->sendReservationApprovalEmail($user, $book, $newDeadline, true);

                    Log::info('Reservation Approval: Reservation approved successfully (Available)', [
                        'user_id' => Auth::guard('admin')->id(),
                        'transaction_id' => $id,
                        'book_title' => $book->title,
                        'requester_email' => $user->email,
                        'timestamp' => now(),
                    ]);

                    return redirect()->back()->with('toast-success', 'Reservation request for ' . $book->title . ' has been approved. Status is set to "Available for pick up".');
                } else {
                    // Book is currently borrowed
                    $transaction->status = 'Reserved';
                    $transaction->reserved_date = now()->format('Y-m-d');
                    $transaction->remarks = $transaction->remarks . ' | RESERVATION APPROVED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s') . ' (Waiting for Return)';
                    $transaction->save();

                    $this->sendReservationApprovalEmail($user, $book, null, false);

                    Log::info('Reservation Approval: Reservation approved successfully (Waiting)', [
                        'user_id' => Auth::guard('admin')->id(),
                        'transaction_id' => $id,
                        'book_title' => $book->title,
                        'requester_email' => $user->email,
                        'timestamp' => now(),
                    ]);

                    return redirect()->back()->with('toast-success', 'Reservation request for ' . $book->title . ' has been approved. The user is now in line for this book.');
                }
            } else {
                // Extension Request Approval: Update due date
                $newDueDate = $transaction->requested_due_date;
                $transaction->due_date = $newDueDate; // Update the actual due date
                $transaction->status = 'Borrowed';
                $transaction->remarks = $transaction->remarks . ' | EXTENSION APPROVED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s');
                $transaction->save();

                $this->sendApprovalEmail($user, $book, $newDueDate);

                Log::info('Extension Approval: Extension approved successfully', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $id,
                    'book_title' => $book->title,
                    'requester_email' => $user->email,
                    'new_due_date' => $newDueDate,
                    'timestamp' => now(),
                ]);

                $formattedDueDate = ($newDueDate instanceof Carbon)
                    ? $newDueDate->format('M d, Y')
                    : Carbon::parse($newDueDate)->format('M d, Y');
                return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been approved. New due date: ' . $formattedDueDate);
            }
        } catch (\Exception $e) {
            Log::error('Reservation/Extension Approval: Failed to approve request', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * Reject reservation or extension request
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reservation/Extension Rejection: Attempting to reject request', [
            'user_id' => Auth::guard('admin')->id(),
            'transaction_id' => $id,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $transaction = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Pending')
            ->findOrFail($id);

        try {
            $user = $transaction->user;
            $book = $transaction->book;

            if ($transaction->transaction_type === 'Reserved') {
                // Book Reservation Rejection
                $transaction->status = 'Cancelled';
                $transaction->remarks = $transaction->remarks . ' | RESERVATION REJECTED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s') . '. Reason: ' . $validated['rejection_reason'];
                $transaction->save();

                $this->sendReservationRejectionEmail($user, $book, $validated['rejection_reason']);

                Log::info('Reservation Rejection: Reservation rejected successfully', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $id,
                    'book_title' => $book->title,
                    'requester_email' => $user->email,
                    'rejection_reason' => $validated['rejection_reason'],
                    'timestamp' => now(),
                ]);

                return redirect()->back()->with('toast-success', 'Reservation request for ' . $book->title . ' has been rejected.');
            } else {
                // Extension Request Rejection
                // Keep the original due_date, just change status back to Borrowed
                $transaction->status = 'Borrowed';
                $transaction->requested_due_date = null; // Clear the requested date
                $transaction->remarks = $transaction->remarks . ' | EXTENSION REJECTED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s') . '. Reason: ' . $validated['rejection_reason'];
                $transaction->save();

                $this->sendRejectionEmail($user, $book, $validated['rejection_reason']);

                Log::info('Extension Rejection: Extension rejected successfully', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $id,
                    'book_title' => $book->title,
                    'requester_email' => $user->email,
                    'rejection_reason' => $validated['rejection_reason'],
                    'timestamp' => now(),
                ]);

                return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been rejected.');
            }
        } catch (\Exception $e) {
            Log::error('Reservation/Extension Rejection: Failed to reject request', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to reject request. Please try again.');
        }
    }

    /**
     * Get extension request statistics
     */
    public function getStatistics()
    {
        Log::debug('Reservation Extension: Fetching statistics API', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        $pending = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Pending')
            ->count();

        $approved = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Renew')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $rejected = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Borrowed')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $total = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Borrowed')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'total_this_month' => $total,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Send approval email to user for extension
     */
    private function sendApprovalEmail($user, $book, $newDueDate)
    {
        if ($user->email) {
            try {
                $dueDate = $newDueDate instanceof Carbon ? $newDueDate : Carbon::parse($newDueDate);

                Mail::to($user->email)->send(new ReservationMail(
                    $user,
                    $book,
                    'Your book extension request has been approved by the library. Your new due date is ' . $dueDate->format('M d, Y'),
                    'extended',
                    $dueDate,
                    $book->book_condition ?? 'Good',
                    0.00,
                    'No Penalty'
                ));
                Log::info('Reservation Extension: Approval email sent', [
                    'recipient_email' => $user->email,
                    'book_title' => $book->title,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation Extension: Failed to send approval email', [
                    'recipient_email' => $user->email,
                    'error_message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);
            }
        }
    }

    /**
     * Send reservation approval email to user
     */
    private function sendReservationApprovalEmail($user, $book, $deadlineDate = null, $isAvailable = true)
    {
        if ($user->email) {
            try {
                if ($isAvailable) {
                    $deadline = $deadlineDate instanceof Carbon ? $deadlineDate : Carbon::parse($deadlineDate);
                    $message = 'Your book reservation request for "' . $book->title . '" has been approved. The book is now available for pickup until ' . $deadline->format('M d, Y') . '.';
                    $approvedMsg = 'Good news! Your book reservation request has been approved and is ready for pickup.';
                } else {
                    $deadline = now(); // Fallback for the mail layout which requires a date
                    $message = 'Your book reservation request for "' . $book->title . '" has been approved. The book is currently borrowed by another user. You will be notified once it becomes available for pickup.';
                    $approvedMsg = 'Good news! Your book reservation request has been approved. You are now in line for this book.';
                }

                Mail::to($user->email)->send(new ReservationMail(
                    $user,
                    $book,
                    $message,
                    'extended',
                    $deadline,
                    $book->book_condition ?? 'Good',
                    0.00,
                    'No Penalty',
                    [
                        'subject' => '✅ Book Reservation Request Approved',
                        'title' => 'Book Reservation Approved',
                        'greeting' => "Dear {$user->first_name} {$user->last_name},",
                        'approved_msg' => $approvedMsg,
                    ]
                ));
                Log::info('Reservation: Approval email sent', [
                    'recipient_email' => $user->email,
                    'book_title' => $book->title,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation: Failed to send approval email', [
                    'recipient_email' => $user->email,
                    'error_message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);
            }
        }
    }

    /**
     * Send rejection email to user for extension
     */
    private function sendRejectionEmail($user, $book, $rejectionReason)
    {
        if ($user->email) {
            try {
                $emailMessage = 'Your book extension request for "' . $book->title . '" has been rejected. Reason: ' . $rejectionReason . '. Please contact the library for more information.';

                Mail::to($user->email)->send(new ReservationMail(
                    $user,
                    $book,
                    $emailMessage,
                    'rejected',
                    $book->due_date ?? now(),
                    $book->book_condition ?? 'Good',
                    0.00,
                    'Pending'
                ));
                Log::info('Reservation Extension: Rejection email sent', [
                    'recipient_email' => $user->email,
                    'book_title' => $book->title,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation Extension: Failed to send rejection email', [
                    'recipient_email' => $user->email,
                    'error_message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);
            }
        }
    }

    /**
     * Send reservation rejection email to user
     */
    private function sendReservationRejectionEmail($user, $book, $rejectionReason)
    {
        if ($user->email) {
            try {
                $emailMessage = 'Your book reservation request for "' . $book->title . '" has been rejected. Reason: ' . $rejectionReason . '. Please contact the library for more information.';

                Mail::to($user->email)->send(new ReservationMail(
                    $user,
                    $book,
                    $emailMessage,
                    'rejected',
                    now(),
                    $book->book_condition ?? 'Good',
                    0.00,
                    'Pending',
                    [
                        'subject' => '❌ Book Reservation Request Rejected',
                        'title' => 'Book Reservation Rejected',
                        'greeting' => "Dear {$user->first_name} {$user->last_name},",
                        'rejected_msg' => 'Unfortunately, your book reservation request has been rejected.',
                    ]
                ));
                Log::info('Reservation: Rejection email sent', [
                    'recipient_email' => $user->email,
                    'book_title' => $book->title,
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation: Failed to send rejection email', [
                    'recipient_email' => $user->email,
                    'error_message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);
            }
        }
    }

    /**
     * Search extension or reservation requests
     */
    public function search(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $activeTab = $request->input('tab', 'reservations');
        if (!in_array($activeTab, ['reservations', 'extensions'])) {
            $activeTab = 'reservations';
        }

        Log::info('Reservation/Extension Approvals: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'search_term' => $request->search,
            'tab' => $activeTab,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
            'perPage' => 'sometimes|integer|min:1|max:500',
            'tab' => 'sometimes|string|in:reservations,extensions',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $query = Transaction::where('status', 'Pending');
        if ($activeTab === 'reservations') {
            $query->where('transaction_type', 'Reserved');
        } else {
            $query->where('transaction_type', 'Borrowed');
        }
            
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($sq) use ($searchTerm) {
                    $sq->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                })->orWhereHas('book', function ($sq) use ($searchTerm) {
                    $sq->where('title', 'like', $searchTerm)
                        ->orWhere('accession', 'like', $searchTerm);
                });
            });
        }

        $pendingRequests = $query->with(['user', 'book'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'tab' => $activeTab,
                'search' => $request->search
            ]);

        // Re-calculate statistics for the index layout
        $pendingReservationsCount = Transaction::where('transaction_type', 'Reserved')
            ->where('status', 'Pending')
            ->count();

        $pendingExtensionsCount = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();

        $pendingExtensionCount = $pendingReservationsCount + $pendingExtensionsCount;
        
        $approvedCount = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Borrowed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        
        $activeBorrowings = Transaction::whereIn('transaction_type', ['Borrowed', 'Reserved'])
            ->where('status', 'Borrowed')
            ->count();

        return view('maintenance.reservations.index', compact(
            'pendingRequests',
            'pendingReservationsCount',
            'pendingExtensionsCount',
            'pendingExtensionCount',
            'approvedCount',
            'activeBorrowings',
            'perPage',
            'activeTab'
        ));
    }
}
