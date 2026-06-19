<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = DiningTable::orderBy('name')->get();

        return view('theme::pages.waiter.tables.index', compact('tables'));
    }

    public function show(DiningTable $table): View
    {
        $table->load(['orders' => fn ($q) => $q->whereIn('status', ['open', 'sent'])->with('items.product')]);

        $activeOrder = $table->activeOrder()?->load('items.product');

        return view('theme::pages.waiter.tables.show', compact('table', 'activeOrder'));
    }
}
