<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\KitchenService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orders,
        protected KitchenService $kitchen,
    ) {}

    public function create(DiningTable $table): RedirectResponse
    {
        $order = $this->orders->openOrder($table, $this->authUser());

        return redirect()
            ->route('waiter.tables.show', $table)
            ->with('success', 'Adisyon açıldı.');
    }

    public function storeItem(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $this->orders->addItem($order, $product, $validated['qty'], $validated['notes'] ?? null);

        return back()->with('success', 'Ürün adisyona eklendi.');
    }

    public function removeItem(Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        try {
            $this->orders->removeItem($order, $item);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('menu.item_removed'));
    }

    public function updateItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            $this->orders->updateItemQty($order, $item, $validated['qty']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('menu.item_qty_updated'));
    }

    public function sendToKitchen(Order $order): RedirectResponse
    {
        $this->orders->sendToKitchen($order);

        return back()->with('success', 'Sipariş mutfağa gönderildi.');
    }

    public function requestPayment(Order $order): RedirectResponse
    {
        try {
            $this->orders->requestPayment($order);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('waiter.tables.index')
            ->with('success', __('menu.bill_sent_to_cashier'));
    }

    public function close(Order $order): RedirectResponse
    {
        try {
            $this->orders->voidEmptyOrder($order);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('waiter.tables.index')
            ->with('success', __('menu.empty_bill_closed'));
    }

    public function markServed(Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        $item->update(['status' => OrderItemStatus::Served]);

        return back()->with('success', 'Ürün servis edildi olarak işaretlendi.');
    }

    public function products(): View
    {
        $categories = Category::with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('name')])->orderBy('sort_order')->get();

        return view('theme::pages.waiter.products.index', compact('categories'));
    }

    public function pollReadyItems(): JsonResponse
    {
        $items = $this->kitchen->readyItems()->map(fn (OrderItem $item) => [
            'id' => $item->id,
            'order_id' => $item->order_id,
            'table_id' => $item->order->cafe_table_id,
            'table' => $item->order->cafeTable->name,
            'product' => $item->product->name,
            'qty' => $item->qty,
        ]);

        return response()->json([
            'items' => $items,
            'tables' => $this->kitchen->readyCountsByTableId(),
        ]);
    }
}
