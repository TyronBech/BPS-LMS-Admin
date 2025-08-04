<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\BookAudit;
use Illuminate\Http\Request;

class BookAuditController extends Controller
{
    public function index(Request $request)
    {
        $data = BookAudit::with('book', 'changedBy', 'oldCategory', 'newCategory')->orderBy('created_at', 'desc')->get();
        return view('report.audits.books.index', compact('data'));
    }
}
