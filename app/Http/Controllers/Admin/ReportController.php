<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $date = $request->date('date') ?? now();

        return view('theme::pages.admin.reports.index', [
            'date' => $date,
            'dailyRevenue' => $reports->dailyRevenue($date),
            'openOrders' => $reports->openOrderCount(),
            'topProducts' => $reports->topProducts(10),
        ]);
    }
}
