<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\UserAudit;
use Illuminate\Http\Request;

class UserAuditController extends Controller
{
    public function index(Request $request)
    {
        $data = UserAudit::with('user', 'changedBy')->orderBy('created_at', 'desc')->get();
        return view('report.audits.users.index', compact('data'));
    }
    private function generateData(Request $request)
    {
        
    }
}
