<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\PaymentConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function store(Request $request, Order $order): RedirectResponse
    {
        if (! in_array($order->status->value, OrderStatus::payableValues(), true)) {
            return back()->with('error', __('menu.order_already_closed'));
        }

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(PaymentConfig::methodValues())],
            'split_count' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->orders->closeOrder(
            $order,
            $validated['payment_method'],
            (int) $validated['split_count'],
        );

        return redirect()
            ->route('cashier.tables.index')
            ->with('success', __('menu.payment_completed'));
    }
}
