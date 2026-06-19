<?php

namespace App\Http\Controllers\Kitchen;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function __construct(protected KitchenService $kitchen) {}

    public function index(): View
    {
        $items = $this->kitchen->pendingItems();

        return view('theme::pages.kitchen.index', compact('items'));
    }

    public function poll(): JsonResponse
    {
        $items = $this->kitchen->pendingItems()->map(fn ($item) => [
            'id' => $item->id,
            'table' => $item->order->cafeTable->name,
            'product' => $item->product->name,
            'qty' => $item->qty,
            'status' => $item->status->value,
            'status_label' => $item->status->label(),
            'notes' => $item->notes,
            'created_at' => $item->created_at->format('H:i'),
        ]);

        return response()->json(['items' => $items]);
    }

    public function updateStatus(Request $request, OrderItem $item): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:preparing,ready'],
        ]);

        $status = OrderItemStatus::from($validated['status']);
        $updated = $this->kitchen->updateItemStatus($item, $status);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $updated->id,
                'status' => $updated->status->value,
                'status_label' => $updated->status->label(),
            ],
        ]);
    }
}
