<?php

namespace App\Http\Controllers\Maintenance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PenaltyRuleController extends Controller
{
    public function index()
    {
        return view('maintenance.penalties.index');
    }
}
