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
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $order = $this->orders->recordPayment(
            $order,
            $validated['payment_method'],
            (int) $validated['split_count'],
            (float) ($validated['discount_percent'] ?? 0),
        );

        if ($order->status === OrderStatus::Closed) {
            return redirect()
                ->route('cashier.tables.index')
                ->with('success', __('menu.payment_completed'));
        }

        return redirect()
            ->route('cashier.tables.show', $order->cafe_table_id)
            ->with('success', __('menu.split_payment_received', [
                'paid' => $order->split_paid_count,
                'total' => $order->split_count,
            ]));
    }
}
