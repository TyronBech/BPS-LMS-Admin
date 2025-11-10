<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Mail\ReservationMail;
use App\Models\StudentDetail;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationExtensionController extends Controller
{
    /**
     * Show all pending extension requests
     */
    public function index(Request $request)
    {   
        $perPage = $request->input('perPage', 10);
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

        Log::info('Pending extension count retrieved: ' . $pendingExtensionCount);

        return view('maintenance.reservations.index', compact(
            'pendingRequests', 
            'pendingExtensionCount', 
            'approvedCount',
            'activeBorrowings',
            'perPage'
        ));
    }

    /**
     * Approve extension request
     */
    public function approve($id)
    {
        $transaction = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->findOrFail($id);

        try {
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

            Log::info("Extension request ID {$id} approved by librarian: " . Auth::user()->email);


            // Ensure newDueDate is formatted as string for the response
            $formattedDueDate = ($newDueDate instanceof Carbon)
                ? $newDueDate->format('M d, Y')
                : Carbon::parse($newDueDate)->format('M d, Y');
            return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been approved. New due date: ' . $formattedDueDate);
        } catch (\Exception $e) {
            Log::error('Failed to approve extension request: ' . $e->getMessage());
            return redirect()->back()->with('toast-error', 'Failed to approve extension request. Please try again.');
        }
    }

    /**
     * Reject extension request
     */
    public function reject(Request $request, $id)
    {
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

            Log::info("Extension request ID {$id} rejected by librarian: " . Auth::user()->email . " | Reason: " . $validated['rejection_reason']);

            return redirect()->back()->with('toast-success', 'Extension request for ' . $book->title . ' has been rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject extension request: ' . $e->getMessage());
            return redirect()->back()->with('toast-error', 'Failed to reject extension request. Please try again.');
        }
    }

    /**
     * Get extension request statistics
     */
    public function getStatistics()
    {
        $pending = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
            ->count();

        $approved = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Borrowed')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $rejected = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Borrowed')
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $total = Transaction::where('transaction_type', 'Borrowed')
            ->where('status', 'Pending')
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
                Log::info("Approval email sent to: {$user->email}");
            } catch (\Exception $e) {
                Log::error('Failed to send approval email: ' . $e->getMessage());
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
                $message = 'Your book extension request for "' . $book->title . '" has been rejected. Reason: ' . $rejectionReason . '. Please contact the library for more information.';

                Mail::to($user->email)->send(new ReservationMail(
                    $user,
                    $book,
                    $message,
                    'extension_rejected',
                    $book->due_date ?? now(),
                    '',
                    '',
                    'Pending'
                ));
                Log::info("Rejection email sent to: {$user->email}");
            } catch (\Exception $e) {
                Log::error('Failed to send rejection email: ' . $e->getMessage());
            }
        }
    }

    /**
     * Search extension requests
     */
    public function search(Request $request)
    {
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

        return view('librarian.pending-extensions', compact('pendingRequests'));
    }
}
