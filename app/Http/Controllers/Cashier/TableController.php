<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Support\PaymentConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(Request $request): View
    {
        $query = DiningTable::query()->orderBy('name');

        if ($search = trim((string) $request->query('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $tables = $query->with(['payableOrder', 'upcomingReservations'])->get();

        return view('theme::pages.cashier.tables.index', [
            'tables' => $tables,
            'search' => $search,
        ]);
    }

    public function show(DiningTable $table): View|RedirectResponse
    {
        $activeOrder = $table->activeOrder()?->load(['items.product', 'cafeTable']);

        if (! $activeOrder) {
            return redirect()
                ->route('cashier.tables.index')
                ->with('error', __('menu.no_open_order'));
        }

        return view('theme::pages.cashier.tables.show', [
            'table' => $table,
            'order' => $activeOrder,
            'paymentMethods' => PaymentConfig::methodOptions(),
        ]);
    }
}
