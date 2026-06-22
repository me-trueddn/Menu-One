<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Services\KitchenService;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function __construct(private KitchenService $kitchen) {}

    public function index(): View
    {
        $tables = DiningTable::query()->orderBy('name')->get();
        $openOrders = Order::query()
            ->with(['cafeTable', 'items.product', 'user'])
            ->whereIn('status', OrderStatus::payableValues())
            ->whereDate('created_at', today())
            ->orderByDesc('updated_at')
            ->get();
        $kitchenTables = $this->kitchen->pendingGroupedByTable();
        $readyTables = $this->kitchen->readyGroupedByTable();

        return view('theme::pages.admin.operations.index', compact(
            'tables',
            'openOrders',
            'kitchenTables',
            'readyTables',
        ));
    }
}
