<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationOrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\IntegrationOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    public function __construct(private IntegrationOrderService $integrationOrders) {}

    public function index(): View
    {
        $orders = $this->deliveryOrdersQuery()->get();

        return view('theme::pages.admin.delivery-orders.index', compact('orders'));
    }

    public function poll(): JsonResponse
    {
        $orders = $this->deliveryOrdersQuery()->get()->map(fn (Order $order) => [
            'id' => $order->id,
            'provider' => $order->integration_provider?->label(),
            'external_order_id' => $order->external_order_id,
            'status' => $order->integration_status?->value,
            'status_label' => $order->integration_status?->label(),
            'customer_name' => $order->customer_name,
            'total' => (float) $order->total,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ]);

        return response()->json(['orders' => $orders]);
    }

    public function accept(Order $order): RedirectResponse
    {
        return $this->runAction(fn () => $this->integrationOrders->accept($order), __('menu.integration_order_accepted'));
    }

    public function preparing(Order $order): RedirectResponse
    {
        return $this->runAction(fn () => $this->integrationOrders->markPreparing($order), __('menu.integration_order_preparing'));
    }

    public function readyForCourier(Order $order): RedirectResponse
    {
        return $this->runAction(fn () => $this->integrationOrders->markReadyForCourier($order), __('menu.integration_order_ready_courier'));
    }

    public function handToCourier(Order $order): RedirectResponse
    {
        return $this->runAction(fn () => $this->integrationOrders->handToCourier($order), __('menu.integration_order_handed_courier'));
    }

    public function reject(Request $request, Order $order): RedirectResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return $this->runAction(
            fn () => $this->integrationOrders->reject($order, $request->input('reason')),
            __('menu.integration_order_rejected'),
        );
    }

    protected function runAction(callable $action, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    protected function deliveryOrdersQuery()
    {
        return Order::query()
            ->with(['items.product', 'cafeTable'])
            ->where('order_type', OrderType::Delivery)
            ->whereNotNull('integration_provider')
            ->whereIn('integration_status', IntegrationOrderStatus::activeValues())
            ->whereDate('created_at', today())
            ->orderByDesc('created_at');
    }
}
