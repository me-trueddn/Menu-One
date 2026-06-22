<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = DiningTable::query()
            ->with(['upcomingReservations', 'payableOrder'])
            ->orderBy('name')
            ->get();

        return view('theme::pages.waiter.tables.index', compact('tables'));
    }

    public function show(DiningTable $table): View
    {
        $table->load(['payableOrder.items.product', 'upcomingReservations.user']);

        $activeOrder = $table->activeOrder();
        $upcomingReservations = $table->upcomingReservations;

        $categories = Category::query()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        return view('theme::pages.waiter.tables.show', compact('table', 'activeOrder', 'categories', 'upcomingReservations'));
    }
}
