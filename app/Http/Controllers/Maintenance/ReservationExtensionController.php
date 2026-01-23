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
     * Show all pending extension requests
     */
    public function index(Request $request)
    {   
        $perPage = $request->input('perPage', 10);

        Log::info('Reservation Extension: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'per_page' => $perPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'sometimes|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $pendingRequests = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->with(['user', 'book'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $pendingExtensionCount = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();
        
        // Count approved extensions this month
        $approvedCount = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Borrowed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        
        // Count active borrowings
        $activeBorrowings = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Borrowed')
            ->count();

        Log::debug('Reservation Extension: Statistics retrieved', [
            'pending_count' => $pendingExtensionCount,
            'approved_month_count' => $approvedCount,
            'active_borrowings' => $activeBorrowings,
            'timestamp' => now(),
        ]);

        return view('maintenance.reservations.index', compact(
            'pendingRequests', 
            'pendingExtensionCount', 
            'approvedCount',
            'activeBorrowings',
            'perPage'
        ));
    }
    public function pendingExtensionCount()
    {
        $count = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();

        return response()->json(['pending_extension_count' => $count]);
    }

    /**
     * Approve extension request
     */
    public function approve($id)
    {
        Log::info('Reservation Extension: Attempting to approve extension', [
            'user_id' => Auth::guard('admin')->id(),
            'transaction_id' => $id,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $transaction = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->findOrFail($id);

        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $user = $transaction->user;
            $book = $transaction->book;
            $studentDetails = StudentDetail::where('user_id', $user->id)->first();
            $studentSection = $studentDetails ? $studentDetails->level . ' - ' . $studentDetails->section : 'N/A';
            // Use the requested_due_date as the new due date
            $newDueDate = $transaction->requested_due_date;
            $transaction->due_date = $newDueDate; // Update the actual due date
            $transaction->status = 'Borrowed';
            $transaction->remarks = $transaction->remarks . ' | APPROVED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s');
            $transaction->save();

            $this->sendApprovalEmail($user, $book, $newDueDate, $studentSection);

            Log::info('Reservation Extension: Extension approved successfully', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'book_title' => $book->title,
                'requester_email' => $user->email,
                'new_due_date' => $newDueDate,
                'timestamp' => now(),
            ]);

            // Ensure newDueDate is formatted as string for the response
            $formattedDueDate = ($newDueDate instanceof Carbon)
                ? $newDueDate->format('M d, Y')
                : Carbon::parse($newDueDate)->format('M d, Y');
            return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been approved. New due date: ' . $formattedDueDate);
        } catch (\Exception $e) {
            Log::error('Reservation Extension: Failed to approve extension', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to approve extension request. Please try again.');
        }
    }

    /**
     * Reject extension request
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reservation Extension: Attempting to reject extension', [
            'user_id' => Auth::guard('admin')->id(),
            'transaction_id' => $id,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $transaction = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->findOrFail($id);

        try {
            $user = $transaction->user;
            $book = $transaction->book;

            // Keep the original due_date, just change status back to Borrowed
            $transaction->status = 'Borrowed';
            $transaction->requested_due_date = null; // Clear the requested date
            $transaction->remarks = $transaction->remarks . ' | REJECTED by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' on ' . now()->format('F d, Y H:i:s') . '. Reason: ' . $validated['rejection_reason'];
            $transaction->save();

            $this->sendRejectionEmail($user, $book, $validated['rejection_reason']);

            Log::info('Reservation Extension: Extension rejected successfully', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'book_title' => $book->title,
                'requester_email' => $user->email,
                'rejection_reason' => $validated['rejection_reason'],
                'timestamp' => now(),
            ]);

            return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been rejected.');
        } catch (\Exception $e) {
            Log::error('Reservation Extension: Failed to reject extension', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Failed to reject extension request. Please try again.');
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

        $pending = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();

        $approved = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Renew')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $rejected = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Borrowed')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $total = Transaction::where('transaction_type', 'Borrowed')
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
     * Send approval email to user
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
                    $newDueDate,
                    '',
                    '',
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
     * Send rejection email to user
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
                    '',
                    '',
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
     * Search extension requests
     */
    public function search(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        Log::info('Reservation Extension: Search performed', [
            'user_id' => Auth::guard('admin')->id(),
            'search_term' => $request->search,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
            'perPage' => 'sometimes|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            return redirect()->route('maintenance.reservations')->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $query = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending');

        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                    ->orWhere('last_name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            })->orWhereHas('book', function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('accession', 'like', $searchTerm);
            });
        }

        $pendingRequests = $query->with(['user', 'book'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('maintenance.reservations.index', compact('pendingRequests'));
    }
}
