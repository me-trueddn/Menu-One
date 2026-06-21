<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\ReservationStatus;
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
        $recentCompletedReservations = $table->reservations()
            ->where('status', ReservationStatus::Completed)
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderByDesc('ends_at')
            ->limit(5)
            ->get();

        $categories = Category::query()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        return view('theme::pages.waiter.tables.show', compact('table', 'activeOrder', 'categories', 'upcomingReservations', 'recentCompletedReservations'));
    }
}
