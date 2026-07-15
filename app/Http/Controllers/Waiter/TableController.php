<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\TableCategory;
use App\Services\KitchenService;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(private KitchenService $kitchen) {}

    public function index(): View
    {
        $categories = TableCategory::query()
            ->with(['tables' => fn ($query) => $query
                ->where('is_virtual', false)
                ->with(['upcomingReservations', 'payableOrder'])
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorizedTables = DiningTable::query()
            ->with(['upcomingReservations', 'payableOrder'])
            ->whereNull('table_category_id')
            ->where('is_virtual', false)
            ->orderBy('name')
            ->get();

        $virtualTables = DiningTable::query()
            ->with(['payableOrder'])
            ->where('is_virtual', true)
            ->orderByDesc('updated_at')
            ->get();

        $readyCountsByTable = $this->kitchen->readyCountsByTableId();

        return view('theme::pages.waiter.tables.index', compact('categories', 'uncategorizedTables', 'virtualTables', 'readyCountsByTable'));
    }

    public function show(DiningTable $table): View
    {
        $table->load(['payableOrder.items.product', 'upcomingReservations.user']);

        $activeOrder = $table->activeOrder();
        $upcomingReservations = $table->upcomingReservations;

        $categories = Category::query()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        return view('theme::pages.waiter.tables.show', compact('table', 'activeOrder', 'categories', 'upcomingReservations'));
    }
}
