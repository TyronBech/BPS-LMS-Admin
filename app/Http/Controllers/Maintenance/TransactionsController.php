<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Book;

class TransactionsController extends Controller
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
        return view('maintenance.transactions.index', compact('transactions', 'transactionTypes', 'transactionStatuses', 'conditions'));
    }
    public function viewTransation(Request $request)
    {
        $transaction = Transaction::with('user', 'book')
            ->where('id', $request->id)
            ->firstOrFail();
        return view('maintenance.transactions.view', compact('transaction'));
    }
    private function extract_enums($table, $columnName){
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
