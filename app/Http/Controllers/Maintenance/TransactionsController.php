<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function index()
    {
        // This method will return the view for managing transactions
        return view('maintenance.transactions.index');
    }
}
