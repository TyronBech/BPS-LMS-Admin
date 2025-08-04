<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\TransactionAudit;
use Illuminate\Http\Request;

class TransactionAuditController extends Controller
{
    public function index(Request $request)
    {
        $data = TransactionAudit::with('transaction', 'changedBy')->orderBy('created_at', 'desc')->get();
        return view('report.audits.transactions.index', compact('data'));
    }
}
