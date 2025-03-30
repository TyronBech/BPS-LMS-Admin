<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use DateTime;

class InventoriesController extends Controller
{
    public function index(){
        $fromInputDate  = null;
        $toInputDate    = null;
        $data           = Inventory::with('book')->orderBy('created_at', 'desc')->get();
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data'));
    }
    public function search(Request $request){
        $fromInputDate  = $request->input('start');
        $toInputDate    = $request->input('end');
        $start          = DateTime::createFromFormat('m/d/Y', $request->input('start'))->format('Y-m-d');
        $end            = DateTime::createFromFormat('m/d/Y', $request->input('end'))->format('Y-m-d');
        $data           = Inventory::with('book')->whereBetween(DB::raw('DATE(inventories.checked_at)'), [$start, $end])->get();
        return view('report.inventories.index', compact('fromInputDate', 'toInputDate', 'data'));
    }
}
