<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\ImportProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportProgressController extends Controller
{
    /**
     * Cancel an active import progress record.
     */
    public function cancel(Request $request, int $id)
    {
        $progress = ImportProgress::find($id);

        if (!$progress) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Import record not found.',
                ], 404);
            }
            return redirect()->back()->with('toast-error', 'Import record not found.');
        }

        if (!$progress->isActive()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'This import is no longer active.',
                    'status'  => $progress->status,
                ]);
            }
            return redirect()->back()->with('toast-warning', 'This import is no longer active.');
        }

        $deletedQueuedJobs = DB::table('jobs')
            ->where(function ($query) use ($id) {
                $query->where('payload', 'like', '%"progressId":' . $id . '%')
                    ->orWhere('payload', 'like', '%"progressId";i:' . $id . '%');
            })
            ->delete();

        $progress->update([
            'status'        => 'cancelled',
            'error_message' => 'Import cancelled by the user.',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'             => true,
                'message'             => $deletedQueuedJobs > 0
                    ? 'Import cancelled before the queue started processing it.'
                    : 'Import cancellation requested. The worker will stop after the current row or chunk.',
                'status'              => 'cancelled',
                'deleted_queued_jobs' => $deletedQueuedJobs,
            ]);
        }

        return redirect()->back()->with('toast-success', 'Import cancelled successfully.');
    }
}
