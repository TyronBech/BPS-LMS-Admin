<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoriesController extends Controller
{
    public function index(){
        return view('report.inventories.index');
    }
}
