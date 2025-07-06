<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\Book;
use DateTime;

class TransactionMaintenanceController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user', 'book')
            ->orderBy('created_at', 'desc')
            ->get();
        $transaction            = new Transaction();
        $books                  = new Book();
        $transactionTypes       = $this->extract_enums($transaction->getTable(), 'transaction_type');
        $transactionStatuses    = $this->extract_enums($transaction->getTable(), 'status');
        $conditions             = $this->extract_enums($books->getTable(), 'condition_status');
        $penaltyStatuses        = $this->extract_enums($transaction->getTable(), 'penalty_status');
        return view('maintenance.transactions.index', compact('transactions', 'transactionTypes', 'transactionStatuses', 'conditions', 'penaltyStatuses'));
    }
    public function show(Request $request)
    {
        $transaction = Transaction::with('user', 'book')
            ->where('id', $request->input('viewBtn'))
            ->firstOrFail();
        return view('maintenance.transactions.view', compact('transaction'));
    }
    public function retrieve(Request $request)
    {
        $transaction = Transaction::with('user', 'book')
            ->where('id', $request->input('viewBtn'))
            ->firstOrFail();
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json([
            'transaction' => $transaction
        ]);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'due_date'          => 'required|date',
            'pickup_date'       => 'nullable|date',
            'transaction_type' => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'status'            => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'status')),
            'book_condition'    => 'nullable|in:' . implode(',', $this->extract_enums((new Book())->getTable(), 'condition_status')),
            'penalty_total'     => 'nullable|numeric|min:0',
            'remarks'           => 'nullable|string|max:2048',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            $transaction = Transaction::find($request->input('edit_transaction_id'));
            if (!$transaction) {
                return redirect()->back()->with('toast-error', 'Transaction not found');
            }
            $transaction->update([
                'due_date'          => DateTime::createFromFormat('m/d/Y', $request->input('due_date'))->format('Y-m-d'),
                'pickup_date'       => $request->input('pickup_date') ? DateTime::createFromFormat('m/d/Y', $request->input('pickup_date'))->format('Y-m-d') : null,
                'transaction_type'  => $request->input('transcaction_type'),
                'status'            => $request->input('status'),
                'book_condition'    => $request->input('book_condition') ?? null,
                'penalty_total'     => $request->input('penalty_total') ?? 0,
                'remarks'           => $request->input('remarks') ?? null,
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Transaction updated successfully');
    }
    private function extract_enums($table, $columnName)
    {
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];

        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        return $enumValues;
    }
}
