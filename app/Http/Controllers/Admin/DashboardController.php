<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        return view('theme::pages.admin.dashboard', [
            'dailyRevenue' => $reports->dailyRevenue(),
            'openOrders' => $reports->openOrderCount(),
            'topProducts' => $reports->topProducts(5),
        ]);
    }
}
